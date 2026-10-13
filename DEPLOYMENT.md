# Guide de Déploiement - Portfolio Mahoulolo SEBIO

## 🚀 Déploiement en Production

### Prérequis
- Serveur web (Apache/Nginx)
- PHP 8.2+
- Composer
- SSL Certificate (recommandé)

### Étapes de Déploiement

#### 1. Préparation du Serveur
```bash
# Mise à jour du système
sudo apt update && sudo apt upgrade -y

# Installation de PHP et extensions
sudo apt install php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd

# Installation de Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### 2. Configuration Nginx
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/portfolio-sebio/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### 3. Configuration Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

#### 4. Déploiement du Code
```bash
# Cloner le projet
git clone <your-repo-url> /var/www/portfolio-sebio
cd /var/www/portfolio-sebio

# Installer les dépendances
composer install --optimize-autoloader --no-dev

# Configuration de l'environnement
cp .env.example .env
php artisan key:generate

# Optimisations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permissions
sudo chown -R www-data:www-data /var/www/portfolio-sebio
sudo chmod -R 755 /var/www/portfolio-sebio
sudo chmod -R 775 /var/www/portfolio-sebio/storage
sudo chmod -R 775 /var/www/portfolio-sebio/bootstrap/cache
```

#### 5. Configuration SSL avec Let's Encrypt
```bash
# Installation de Certbot
sudo apt install certbot python3-certbot-nginx

# Génération du certificat SSL
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Test du renouvellement automatique
sudo certbot renew --dry-run
```

### 🔧 Optimisations de Performance

#### 1. Cache Laravel
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

#### 2. Optimisation Composer
```bash
composer install --optimize-autoloader --no-dev
```

#### 3. Compression Gzip (Nginx)
```nginx
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_proxied expired no-cache no-store private must-revalidate auth;
gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript;
```

#### 4. Cache des Assets Statiques
```nginx
location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

### 📊 Monitoring et Maintenance

#### 1. Logs Laravel
```bash
# Surveillance des logs
tail -f storage/logs/laravel.log

# Rotation des logs
sudo logrotate /etc/logrotate.d/laravel
```

#### 2. Surveillance des Performances
```bash
# Installation de htop pour monitoring
sudo apt install htop

# Surveillance de l'espace disque
df -h

# Surveillance de la mémoire
free -h
```

#### 3. Sauvegarde Automatique
```bash
#!/bin/bash
# backup.sh
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/portfolio-sebio"
PROJECT_DIR="/var/www/portfolio-sebio"

mkdir -p $BACKUP_DIR
tar -czf $BACKUP_DIR/portfolio-sebio_$DATE.tar.gz -C $PROJECT_DIR .

# Garder seulement les 7 dernières sauvegardes
find $BACKUP_DIR -name "portfolio-sebio_*.tar.gz" -mtime +7 -delete
```

### 🚨 Sécurité

#### 1. Configuration Firewall
```bash
# UFW (Ubuntu)
sudo ufw enable
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw allow 80
sudo ufw allow 443
```

#### 2. Sécurisation PHP
```ini
; /etc/php/8.2/fpm/php.ini
expose_php = Off
display_errors = Off
log_errors = On
allow_url_fopen = Off
allow_url_include = Off
```

#### 3. Sécurisation Laravel
```bash
# Génération d'une clé d'application forte
php artisan key:generate --force

# Configuration des permissions
sudo chmod 644 /var/www/portfolio-sebio/.env
sudo chown root:root /var/www/portfolio-sebio/.env
```

### 🔄 Mise à Jour

#### 1. Mise à jour du Code
```bash
cd /var/www/portfolio-sebio
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 2. Mise à jour des Dépendances
```bash
composer update
npm update
```

### 📈 Performance et Monitoring

#### 1. Outils de Monitoring
- **New Relic** - APM complet
- **Laravel Telescope** - Debug et monitoring
- **Laravel Horizon** - Queue monitoring
- **Google Analytics** - Analytics web

#### 2. Optimisations Base de Données
```sql
-- Index sur les colonnes fréquemment utilisées
CREATE INDEX idx_created_at ON table_name(created_at);
CREATE INDEX idx_status ON table_name(status);
```

### 🆘 Dépannage

#### Problèmes Courants

1. **Erreur 500**
   ```bash
   # Vérifier les logs
   tail -f storage/logs/laravel.log
   
   # Vérifier les permissions
   sudo chmod -R 775 storage bootstrap/cache
   ```

2. **Assets non chargés**
   ```bash
   # Vérifier les permissions
   sudo chown -R www-data:www-data public/
   
   # Vérifier la configuration Nginx/Apache
   ```

3. **Problèmes de cache**
   ```bash
   # Nettoyer tous les caches
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

### 📞 Support

En cas de problème :
- **Email** : sebiomahoulolo4@gmail.com
- **Téléphone** : +229 01 65 60 30 40

---

**Bon déploiement ! 🚀**
