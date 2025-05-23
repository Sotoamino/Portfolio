# Portfolio# 📦 Portfolio Installer PHP

Ce script `install.php` permet de déployer automatiquement le portfolio PHP depuis GitHub, configurer la base de données, et créer un administrateur.

---

## 🚀 Fonctionnalités

- Téléchargement et extraction du code source depuis GitHub
- Création automatique des tables SQL nécessaires
- Préremplissage de la table `settings` avec vos informations
- Création d’un compte administrateur (id = 1)
- Génération automatique du mot de passe admin (ou mot de passe personnalisé)
- Ouverture automatique du back office (`/admin/login.php`) à la fin de l’installation

---

## 🔧 Prérequis

- Serveur web avec PHP 7.4 ou supérieur (WAMP, XAMPP, etc.)
- MySQL/MariaDB
- Accès à Internet (pour télécharger le dépôt GitHub)

---

## 🛠️ Installation

1. **Télécharger le fichier `install.php`**  
   Placez-le à la racine de votre serveur local (ex : `C:/wamp64/www/install.php`).

2. **Lancer le script dans votre navigateur**  
   Ouvrez :  
   `http://localhost/install.php`

3. **Remplir les champs du formulaire :**

   - **Base de données** : hôte, nom, utilisateur, mot de passe
   - **Vos informations personnelles** : prénom, nom, email, téléphone
   - **Chemin d’installation** : dossier où extraire le projet
   - _(optionnel)_ Mot de passe admin

4. **Cliquez sur “Installer”**

   Le script :
   - Crée les tables MySQL
   - Insère vos infos dans `settings`
   - Crée un compte admin avec identifiant `admin`
   - Affiche vos identifiants admin
   - Ouvre automatiquement la page `/admin/login.php` dans un nouvel onglet

---

## 🔑 Accès admin

Une fois l’installation terminée, les identifiants seront affichés :

Identifiant : admin
Mot de passe : ********


---

## 🧹 Nettoyage

Après installation, **supprimez `install.php`** du serveur pour des raisons de sécurité.

---

## ❓ FAQ

- **Puis-je choisir le mot de passe admin ?**  
  Oui, un champ est prévu dans le formulaire. Sinon, un mot de passe fort est généré automatiquement.

- **Le dossier de destination est vide ?**  
  Vérifiez les permissions d’écriture et que le dépôt GitHub est accessible.

- **L’admin ne se connecte pas ?**  
  Utilisez l’identifiant `admin` et le mot de passe fourni. Vérifiez aussi que le fichier `.htaccess` ne bloque pas l’accès.

---

## 📁 Exemple de structure après installation

mon_portfolio/
├── admin/
│ └── login.php
├── assets/
├── index.php
└── ...


---

## 📝 Licence

Projet sous licence MIT. Développé par *Liam Salamagnon*. Tout droit protégé. Reproduction interdite sans accord du propriétaire du code. Contact : transfert@salamagnon.fr
