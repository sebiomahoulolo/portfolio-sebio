# Portfolio Mahoulolo SEBIO

Un portfolio moderne et interactif développé avec Laravel, featuring des animations 3D impressionnantes et un design responsive.

## 🚀 Fonctionnalités

### ✨ Design & Animations
- **Animations 3D** avec Three.js pour des effets visuels spectaculaires
- **Design responsive** optimisé pour tous les appareils
- **Animations CSS avancées** avec transitions fluides
- **Effets de parallaxe** pour une expérience immersive
- **Loading screen** avec animation de chargement

### 🎨 Interface Utilisateur
- **Navigation fixe** avec effets de transparence
- **Section Hero** avec particules 3D animées
- **Cartes interactives** avec effets hover 3D
- **Barres de compétences** animées
- **Galerie de projets** avec effets de rotation 3D
- **Formulaire de contact** avec validation en temps réel

### 🛠️ Technologies Utilisées

#### Backend
- **Laravel 12** - Framework PHP moderne
- **PHP 8.2+** - Langage de programmation
- **MySQL** - Base de données (optionnelle)

#### Frontend
- **HTML5** - Structure sémantique
- **CSS3** - Styles avancés avec animations
- **JavaScript ES6+** - Interactivité
- **Bootstrap 5** - Framework CSS responsive
- **Three.js** - Rendu 3D et animations
- **GSAP** - Animations avancées
- **AOS** - Animations au scroll

#### Outils de Développement
- **Composer** - Gestionnaire de dépendances PHP
- **NPM** - Gestionnaire de paquets JavaScript
- **Git** - Contrôle de version

## 📋 Prérequis

- PHP 8.2 ou supérieur
- Composer
- Node.js et NPM
- Serveur web (Apache/Nginx) ou serveur de développement Laravel

## 🚀 Installation

### 1. Cloner le projet
```bash
git clone <url-du-repo>
cd portfolio-sebio
```

### 2. Installer les dépendances PHP
```bash
composer install
```

### 3. Installer les dépendances JavaScript
```bash
npm install
```

### 4. Configuration de l'environnement
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configuration de la base de données (optionnelle)
Éditez le fichier `.env` et configurez votre base de données :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=portfolio_sebio
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 6. Exécuter les migrations (si base de données configurée)
```bash
php artisan migrate
```

### 7. Compiler les assets (optionnel)
```bash
npm run build
```

### 8. Démarrer le serveur de développement
```bash
php artisan serve
```

Le portfolio sera accessible à l'adresse : `http://localhost:8000`

## 📁 Structure du Projet

```
portfolio-sebio/
├── app/
│   └── Http/
│       └── Controllers/
│           └── PortfolioController.php
├── public/
│   ├── css/
│   │   └── portfolio.css
│   └── js/
│       └── portfolio.js
├── resources/
│   └── views/
│       └── portfolio/
│           └── index.blade.php
├── routes/
│   └── web.php
└── README.md
```

## 🎯 Sections du Portfolio

### 1. **Hero Section**
- Présentation personnelle avec animations 3D
- Particules flottantes interactives
- Boutons d'action avec effets hover

### 2. **À Propos**
- Description professionnelle
- Informations de contact
- Projet phare (AfriCode)

### 3. **Compétences Techniques**
- **Frontend** : HTML5, CSS3, JavaScript, React.js, Angular, Bootstrap
- **Backend** : Laravel, PHP, MySQL, API REST
- **Outils** : Git/GitHub, NPM, Composer, FedaPay API

### 4. **Projets Réalisés**
- StagesBENIN - Plateforme de stages et emplois
- Africa-Location - Site immobilier
- Gestion d'école - Application de gestion scolaire
- FHC Entreprises - Site institutionnel
- Palais des Merveilles Spirituelles - Site vitrine
- AfriCode - Plateforme éducative
- BBLove - Plateforme de rencontres
- CVLink - Créateur de CV en ligne

### 5. **Contact**
- Formulaire de contact avec validation
- Informations de contact directes

## 🎨 Personnalisation

### Modifier les informations personnelles
Éditez le fichier `app/Http/Controllers/PortfolioController.php` pour modifier :
- Informations personnelles
- Compétences
- Projets
- Expérience professionnelle

### Personnaliser le design
- **Couleurs** : Modifiez les variables CSS dans `public/css/portfolio.css`
- **Animations** : Ajustez les paramètres dans `public/js/portfolio.js`
- **Contenu** : Modifiez les vues Blade dans `resources/views/portfolio/`

### Variables CSS principales
```css
:root {
    --primary-color: #6366f1;
    --secondary-color: #8b5cf6;
    --accent-color: #06b6d4;
    --dark-color: #1e293b;
    --light-color: #f8fafc;
}
```

## 📱 Responsive Design

Le portfolio est entièrement responsive et optimisé pour :
- **Desktop** (1200px+)
- **Tablet** (768px - 1199px)
- **Mobile** (320px - 767px)

## 🚀 Déploiement

### Sur un serveur partagé
1. Uploadez tous les fichiers sur votre serveur
2. Configurez le document root vers le dossier `public`
3. Configurez la base de données dans `.env`
4. Exécutez `php artisan migrate` si nécessaire

### Sur un VPS/Dédié
1. Clonez le projet sur votre serveur
2. Installez les dépendances avec Composer
3. Configurez Nginx/Apache
4. Configurez SSL avec Let's Encrypt

## 🔧 Maintenance

### Mise à jour des dépendances
```bash
composer update
npm update
```

### Optimisation des performances
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📞 Support

Pour toute question ou support :
- **Email** : sebiomahoulolo4@gmail.com
- **Téléphone** : +229 01 65 60 30 40
- **Localisation** : Godomey-Cotonou, Bénin

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 🙏 Remerciements

- **Laravel** - Framework PHP exceptionnel
- **Three.js** - Bibliothèque 3D JavaScript
- **Bootstrap** - Framework CSS responsive
- **Font Awesome** - Icônes vectorielles
- **Google Fonts** - Typographie Inter

---

**Développé avec ❤️ par Mahoulolo SEBIO**

*"Pas de code, pas d'avenir"* - Contribuer à l'écosystème numérique africain