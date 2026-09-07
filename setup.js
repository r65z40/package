const bcrypt = require('bcrypt');
const fs = require('fs');
const path = require('path');
const readline = require('readline');

const USERS_FILE = path.join(__dirname, 'data', 'users.json');

const rl = readline.createInterface({ input: process.stdin, output: process.stdout });

function ask(question) {
  return new Promise(resolve => rl.question(question, resolve));
}

async function setup() {
  console.log('\n=== Cedelia Tools Portal - Configuration ===\n');

  const username = await ask('Nom d\'utilisateur admin : ');
  const password = await ask('Mot de passe admin : ');

  if (!username || !password) {
    console.log('Erreur : nom d\'utilisateur et mot de passe requis.');
    process.exit(1);
  }

  const hash = await bcrypt.hash(password, 12);

  if (!fs.existsSync(path.dirname(USERS_FILE))) {
    fs.mkdirSync(path.dirname(USERS_FILE), { recursive: true });
  }

  fs.writeFileSync(USERS_FILE, JSON.stringify([{ username, password: hash }], null, 2));
  console.log(`\nCompte admin "${username}" créé avec succès.`);
  console.log('Lancez le serveur avec : npm start\n');
  rl.close();
}

setup();
