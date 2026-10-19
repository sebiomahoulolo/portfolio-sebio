# Déploiement sur Hostinger

Ce guide met en ligne le portfolio sur l'hébergement Hostinger (SSH disponible, **pas de Node/npm** sur le serveur). Les assets de l'administration sont donc compilés en local, puis envoyés avec le code.

## Structure sur le serveur

Le dossier `public_html/` du domaine contient **tout le projet Laravel**. Le fichier `.htaccess` à la racine redirige les requêtes vers `public/` et `server.php`. Gardez ces trois fichiers racine (`.htaccess`, `index.php`, `server.php`) tels quels.

## 1. Préparer l'archive (sur votre PC)

Dans Git Bash, à la racine du projet :

```bash
bash scripts/package.sh
```

Le script compile l'administration (`public/build`), lance les tests, puis crée `portfolio-AAAAMMJJ-HHMM.tar.gz`. L'archive ne contient ni `vendor/`, ni `node_modules/`, ni `.env`.

## 2. Première mise en ligne

### 2.1 Créer la base de données et la boîte email

Dans hPanel :

- **Bases de données → MySQL** : créez une base et un utilisateur, et notez les identifiants.
- **Emails** : créez la boîte `contact@mahoulolo-sebio.com` et notez son mot de passe.

### 2.2 Sauvegarder l'ancien site

```bash
cd ~/domains/green-aardvark-956386.hostingersite.com
tar -czf ~/sauvegarde-portfolio-$(date +%Y%m%d).tar.gz public_html
```

### 2.3 Envoyer et extraire l'archive

Envoyez l'archive dans `public_html/` avec le gestionnaire de fichiers, puis :

```bash
cd ~/domains/green-aardvark-956386.hostingersite.com/public_html
tar -xzf portfolio-*.tar.gz && rm portfolio-*.tar.gz
composer install --no-dev --optimize-autoloader --no-interaction
```

Si `composer` est indisponible, lancez `composer install --no-dev` sur votre PC et envoyez aussi le dossier `vendor/`.

### 2.4 Configurer l'environnement

```bash
cp .env.production.example .env
nano .env        # renseigner DB_* et MAIL_PASSWORD
php artisan key:generate --force
```

### 2.5 Initialiser l'application

```bash
php artisan migrate --force          # crée les tables
php artisan db:seed --force          # importe le contenu actuel (profil, projets, parcours)
php artisan storage:link             # rend les images téléversées accessibles
php artisan portfolio:create-owner   # crée VOTRE compte d'administration
php artisan optimize
chmod -R 775 storage bootstrap/cache
```

L'administration est accessible sur **`/admin`**. Il n'existe pas de page d'inscription : seuls les comptes créés avec `portfolio:create-owner` peuvent se connecter.

## 3. Mises à jour suivantes

```bash
# Sur le PC
bash scripts/package.sh

# Sur le serveur, après envoi de l'archive dans public_html/
mysqldump -u <utilisateur> -p <base> > ~/backup-$(date +%Y%m%d).sql
php artisan down
tar -xzf portfolio-*.tar.gz && rm portfolio-*.tar.gz
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize
php artisan up
```

Le seeder ne remplace jamais un contenu déjà modifié depuis l'administration. Il est inutile de le relancer lors des mises à jour.

**Retour arrière** : restaurez l'archive `sauvegarde-portfolio-*.tar.gz` et le dump SQL.

## 4. Tâche planifiée (sauvegardes et nettoyage des données)

Chaque nuit, le site sauvegarde la base et les fichiers (3 h), puis applique les durées de conservation de la politique de confidentialité (3 h 30). Il faut une **tâche Cron** pour que cela fonctionne :

1. hPanel → **Avancé → Tâches Cron** → type « PHP » ou « Personnalisé ».
2. Commande (adaptez le chemin) :
   ```
   /usr/bin/php /home/<utilisateur>/domains/<domaine>/public_html/artisan schedule:run
   ```
3. Fréquence : **toutes les minutes** (`* * * * *`).

Dans `.env`, définissez `BACKUP_PASSWORD` (archives chiffrées en AES-256) et **notez-le en lieu sûr**, avec l’`APP_KEY` : sans elles, une sauvegarde est inutilisable.

Vérification : `php artisan portfolio:backup` crée une archive tout de suite ; elle apparaît dans **Administration → Sauvegardes**, d’où vous pouvez aussi la télécharger. Une alerte s’y affiche si aucune sauvegarde n’a été faite depuis 48 h, et un email est envoyé en cas d’échec.

## 5. Brancher le domaine mahoulolo-sebio.com

1. hPanel → **Sites web → Ajouter un domaine** (ou « Changer de domaine » sur le site existant) : `mahoulolo-sebio.com`.
2. Chez le registraire du domaine : faites pointer les serveurs DNS vers ceux de Hostinger (`ns1.dns-parking.com`, `ns2.dns-parking.com`), ou créez les enregistrements A indiqués dans hPanel.
3. hPanel → **SSL** : activez le certificat gratuit.
4. Vérifiez `APP_URL=https://www.mahoulolo-sebio.com` (avec www : l’adresse sans www y est redirigée) dans `.env`, puis lancez `php artisan optimize`.
5. Soumettez `https://www.mahoulolo-sebio.com/sitemap.xml` dans Google Search Console.

## 6. Vérifications après déploiement

- [ ] La page d'accueil, `/projets` et la page d'un projet s'affichent.
- [ ] Le bouton « Télécharger mon CV » ouvre le PDF.
- [ ] Un message envoyé depuis le formulaire arrive dans `/admin/messages` **et** dans la boîte contact@.
- [ ] Une image téléversée sur un projet s'affiche sur le site (sinon, vérifiez `php artisan storage:link`).
- [ ] `/admin` redirige vers la connexion quand on n'est pas connecté.
- [ ] `storage/logs/` ne contient pas d'erreur.
- [ ] La double authentification est activée dans **Mon compte** (codes de secours notés hors ligne).
- [ ] `php artisan schedule:list` affiche les deux tâches, et une sauvegarde apparaît le lendemain dans **Sauvegardes**.
- [ ] `https://mahoulolo-sebio.com/.env` renvoie une erreur 403.

## 7. Intégration continue (GitHub Actions)

Dépôt : <https://github.com/sebiomahoulolo/portfolio-sebio> (branche `main`).

Le workflow [.github/workflows/deploy.yml](.github/workflows/deploy.yml) s’exécute à chaque push et pull request sur `main` : validation de `composer.json`, audit des failles des dépendances, compilation des assets de l’administration, puis les tests sur une base MySQL **jetable** du runner (jamais la production). Résultats : onglet **Actions** du dépôt.

### Déploiement automatique : pas encore activé

Il sera ajouté seulement après validation de ces points :

| À confirmer | Où le trouver |
|---|---|
| Hôte, port SSH (souvent `65002`) et utilisateur `u…` | hPanel → Avancé → Accès SSH |
| Chemin du site (`~/domains/<domaine>/public_html`) | hPanel → Gestionnaire de fichiers |
| Clé SSH dédiée au déploiement (sans mot de passe) | à générer, clé publique ajoutée dans hPanel → SSH |
| Stratégie de migration : `php artisan migrate --force` automatique, ou manuelle après sauvegarde | à décider |
| Permissions `storage/` et `bootstrap/cache` (775) | après le premier déploiement |

Les secrets iront dans GitHub → **Settings → Secrets and variables → Actions** (`HOSTINGER_HOST`, `HOSTINGER_PORT`, `HOSTINGER_USER`, `HOSTINGER_SSH_KEY`, `HOSTINGER_PATH`), jamais dans le code. Le `.env` de production reste uniquement sur le serveur.
