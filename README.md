# Portfolio — Mahoulolo SEBIO

Site personnel de Mahoulolo SEBIO avec un espace d'administration pour gérer les projets, le profil public et les messages de contact sans toucher au code.

- **Site public** : Blade, CSS et JS sans framework (`public/css/site.css`, `public/js/site.js`), aux couleurs noir `#111111`, blanc cassé `#F5F3EF` et orange cuivré `#D97745`.
- **Administration** (`/admin`) : Livewire 4, Flux (version gratuite) et Tailwind CSS 4, compilés avec Vite.
- **Back-end** : Laravel 12, PHP 8.3, MySQL/MariaDB.

## Fonctionnalités

| Espace | Contenu |
|---|---|
| Site public | Français (`/`) et anglais (`/en`), mode clair / sombre, accueil en grille bento, page `/services` et une page par service, galerie `/projets` filtrable, pages projet, recommandations, CV, `sitemap.xml` bilingue (hreflang), Open Graph et JSON-LD |
| Motion design | Changement de thème par révélation circulaire, transitions entre pages, onde au clic, boutons magnétiques, compteurs, code animé, apparitions au défilement (désactivées si l’utilisateur réduit les animations) |
| Contenu | Profil (accroche, expertises), services, projets, technologies, parcours, recommandations : tout est bilingue et modifiable dans `/admin` |
| Projets | Création et modification, brouillon / publication / publication programmée / archivage, corbeille, couverture et galerie, technologies, SEO, recherche, filtres, tri, pagination, actions groupées |
| Contact | Formulaire « contact » ou « devis », enregistrement en base, email de notification, piège à robots, limite de 5 envois par heure et par IP, boîte de réception dans l'administration |
| Sécurité | Pas d'inscription publique, rôles propriétaire / éditeur, désactivation de compte, limitation des tentatives de connexion, journal d'activité |

### Rôles

- **Propriétaire** : accès complet.
- **Éditeur** : projets et technologies, sans suppression. Pas d'accès aux messages, au profil public ni au journal.

## Installation locale

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# créer la base « portfolio_sebio » dans MySQL, puis :
php artisan migrate --seed
php artisan storage:link
php artisan portfolio:create-owner
npm run build
php artisan serve
```

## Tests

Les tests utilisent la base MySQL `portfolio_sebio_test`, à créer avant le premier lancement :

```bash
php artisan test
```

## Déploiement

Voir [DEPLOYMENT.md](DEPLOYMENT.md).
