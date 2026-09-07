# Cedelia Tools Portal

Portail de téléchargement d'outils pour Cedelia Informatique.

## Installation

```bash
npm install
```

## Configuration

Créez un compte administrateur :

```bash
npm run setup
```

Ou manuellement avec Node :

```bash
node -e "const b=require('bcrypt'),f=require('fs');b.hash('VOTRE_MOT_DE_PASSE',12).then(h=>f.writeFileSync('data/users.json',JSON.stringify([{username:'admin',password:h}],null,2)))"
```

## Lancement

```bash
npm start
```

Le portail est accessible sur `http://localhost:3000`

## Variables d'environnement

| Variable | Description | Défaut |
|---|---|---|
| `PORT` | Port du serveur | `3000` |
| `SESSION_SECRET` | Clé de session | Générée automatiquement |

## Structure

- `/` — Portail public de téléchargement
- `/admin.html` — Interface d'administration (protégée par mot de passe)
- `data/tools.json` — Catalogue des outils
- `data/users.json` — Comptes admin (non versionné)
- `data/logs.json` — Journaux d'activité (non versionné)
- `uploads/` — Fichiers uploadés (non versionné)
