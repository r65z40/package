# Cedelia Tools Portal

Portail de téléchargement d'outils pour Cedelia Informatique.
Fonctionne sur n'importe quel hébergement PHP classique (OVH, O2Switch, etc.) — aucune dépendance, aucune base de données.

## Installation

1. Uploadez tous les fichiers sur votre hébergement via FTP
2. Vérifiez que les dossiers `data/` et `uploads/` sont accessibles en écriture par PHP (chmod 755 ou 775)
3. Accédez à `setup.php` depuis votre navigateur pour créer le compte admin
4. **Supprimez `setup.php`** du serveur après la configuration

## Structure

```
├── index.html          # Portail public
├── admin.html          # Interface admin
├── setup.php           # Installation (à supprimer après usage)
├── api/
│   ├── config.php      # Configuration
│   ├── tools.php       # Liste des outils (public)
│   ├── download.php    # Téléchargement (public)
│   ├── login.php       # Connexion admin
│   ├── logout.php      # Déconnexion
│   ├── auth-check.php  # Vérification session
│   ├── admin-tools.php # Gestion outils (admin)
│   └── admin-logs.php  # Journaux (admin)
├── data/               # Données JSON (protégé par .htaccess)
├── uploads/            # Fichiers uploadés (protégé par .htaccess)
├── css/style.css
└── img/
```

## Prérequis

- PHP 7.4+ (avec `password_hash`, `json_encode`, sessions)
- Hébergement web avec support PHP (Apache recommandé pour les .htaccess)
