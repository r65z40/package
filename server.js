const express = require('express');
const session = require('express-session');
const multer = require('multer');
const bcrypt = require('bcrypt');
const path = require('path');
const fs = require('fs');
const crypto = require('crypto');

const app = express();
const PORT = process.env.PORT || 3000;

const DATA_DIR = path.join(__dirname, 'data');
const UPLOADS_DIR = path.join(__dirname, 'uploads');
const TOOLS_FILE = path.join(DATA_DIR, 'tools.json');
const USERS_FILE = path.join(DATA_DIR, 'users.json');
const LOGS_FILE = path.join(DATA_DIR, 'logs.json');

for (const dir of [DATA_DIR, UPLOADS_DIR]) {
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
}

function readJSON(file) {
  if (!fs.existsSync(file)) return [];
  return JSON.parse(fs.readFileSync(file, 'utf8'));
}

function writeJSON(file, data) {
  fs.writeFileSync(file, JSON.stringify(data, null, 2), 'utf8');
}

if (!fs.existsSync(TOOLS_FILE)) writeJSON(TOOLS_FILE, []);
if (!fs.existsSync(USERS_FILE)) writeJSON(USERS_FILE, []);
if (!fs.existsSync(LOGS_FILE)) writeJSON(LOGS_FILE, []);

function addLog(action, details, req) {
  const logs = readJSON(LOGS_FILE);
  logs.unshift({
    id: crypto.randomUUID(),
    timestamp: new Date().toISOString(),
    action,
    details,
    ip: req.ip || req.connection.remoteAddress
  });
  if (logs.length > 5000) logs.length = 5000;
  writeJSON(LOGS_FILE, logs);
}

const storage = multer.diskStorage({
  destination: UPLOADS_DIR,
  filename: (req, file, cb) => {
    const uniqueName = crypto.randomUUID() + path.extname(file.originalname);
    cb(null, uniqueName);
  }
});

const upload = multer({
  storage,
  limits: { fileSize: 500 * 1024 * 1024 }
});

app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'public')));

app.set('trust proxy', true);

app.use(session({
  secret: process.env.SESSION_SECRET || crypto.randomBytes(32).toString('hex'),
  resave: false,
  saveUninitialized: false,
  cookie: { maxAge: 3600000 }
}));

function requireAuth(req, res, next) {
  if (req.session && req.session.authenticated) return next();
  res.status(401).json({ error: 'Non autorisé' });
}

// --- Public API ---

app.get('/api/tools', (req, res) => {
  const tools = readJSON(TOOLS_FILE);
  res.json(tools.map(t => ({
    id: t.id,
    name: t.name,
    description: t.description,
    category: t.category,
    version: t.version,
    buttonColor: t.buttonColor,
    size: t.size,
    createdAt: t.createdAt
  })));
});

app.get('/api/download/:id', (req, res) => {
  const tools = readJSON(TOOLS_FILE);
  const tool = tools.find(t => t.id === req.params.id);
  if (!tool) return res.status(404).json({ error: 'Outil introuvable' });

  const filePath = path.join(UPLOADS_DIR, tool.filename);
  if (!fs.existsSync(filePath)) return res.status(404).json({ error: 'Fichier introuvable' });

  addLog('download', `Téléchargement de "${tool.name}"`, req);
  res.download(filePath, tool.originalName || tool.name);
});

// --- Auth API ---

app.post('/api/login', async (req, res) => {
  const { username, password } = req.body;
  const users = readJSON(USERS_FILE);
  const user = users.find(u => u.username === username);

  if (!user || !(await bcrypt.compare(password, user.password))) {
    addLog('login_failed', `Tentative de connexion échouée pour "${username || '(vide)'}"`, req);
    return res.status(401).json({ error: 'Identifiants incorrects' });
  }

  req.session.authenticated = true;
  req.session.username = user.username;
  addLog('login', `Connexion réussie de "${user.username}"`, req);
  res.json({ success: true });
});

app.post('/api/logout', (req, res) => {
  const username = req.session.username;
  req.session.destroy();
  addLog('logout', `Déconnexion de "${username}"`, { ip: req.ip });
  res.json({ success: true });
});

app.get('/api/auth/check', (req, res) => {
  res.json({ authenticated: !!(req.session && req.session.authenticated) });
});

// --- Admin API ---

app.post('/api/admin/tools', requireAuth, upload.single('file'), (req, res) => {
  if (!req.file) return res.status(400).json({ error: 'Aucun fichier fourni' });

  const tools = readJSON(TOOLS_FILE);
  const newTool = {
    id: crypto.randomUUID(),
    name: req.body.name || req.file.originalname,
    description: req.body.description || '',
    category: req.body.category || 'Utilitaires',
    version: req.body.version || '1.0',
    buttonColor: req.body.buttonColor || '',
    filename: req.file.filename,
    originalName: req.file.originalname,
    size: req.file.size,
    createdAt: new Date().toISOString()
  };

  tools.push(newTool);
  writeJSON(TOOLS_FILE, tools);
  addLog('tool_add', `Ajout de l'outil "${newTool.name}"`, req);
  res.json(newTool);
});

app.put('/api/admin/tools/:id', requireAuth, (req, res) => {
  const tools = readJSON(TOOLS_FILE);
  const idx = tools.findIndex(t => t.id === req.params.id);
  if (idx === -1) return res.status(404).json({ error: 'Outil introuvable' });

  const allowed = ['name', 'description', 'category', 'version', 'buttonColor'];
  for (const key of allowed) {
    if (req.body[key] !== undefined) tools[idx][key] = req.body[key];
  }

  writeJSON(TOOLS_FILE, tools);
  addLog('tool_edit', `Modification de l'outil "${tools[idx].name}"`, req);
  res.json(tools[idx]);
});

app.delete('/api/admin/tools/:id', requireAuth, (req, res) => {
  const tools = readJSON(TOOLS_FILE);
  const idx = tools.findIndex(t => t.id === req.params.id);
  if (idx === -1) return res.status(404).json({ error: 'Outil introuvable' });

  const tool = tools[idx];
  const filePath = path.join(UPLOADS_DIR, tool.filename);
  if (fs.existsSync(filePath)) fs.unlinkSync(filePath);

  tools.splice(idx, 1);
  writeJSON(TOOLS_FILE, tools);
  addLog('tool_delete', `Suppression de l'outil "${tool.name}"`, req);
  res.json({ success: true });
});

app.get('/api/admin/logs', requireAuth, (req, res) => {
  const logs = readJSON(LOGS_FILE);
  const page = parseInt(req.query.page) || 1;
  const limit = parseInt(req.query.limit) || 50;
  const filter = req.query.filter || '';

  let filtered = logs;
  if (filter) {
    filtered = logs.filter(l =>
      l.action.includes(filter) ||
      l.details.toLowerCase().includes(filter.toLowerCase()) ||
      l.ip.includes(filter)
    );
  }

  const total = filtered.length;
  const start = (page - 1) * limit;
  const paginated = filtered.slice(start, start + limit);

  res.json({ logs: paginated, total, page, totalPages: Math.ceil(total / limit) });
});

app.listen(PORT, () => {
  console.log(`Cedelia Tools Portal - http://localhost:${PORT}`);
});
