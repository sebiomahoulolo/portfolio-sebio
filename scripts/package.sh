#!/usr/bin/env bash
# Prépare l'archive à envoyer sur Hostinger (pas de npm sur le serveur).
# Usage, depuis la racine du projet (Git Bash) : bash scripts/package.sh
set -euo pipefail

cd "$(dirname "$0")/.."

echo "→ Compilation des assets de l'administration"
npm run build

echo "→ Vérification des tests"
php artisan test --no-ansi

release="portfolio-$(date +%Y%m%d-%H%M).tar.gz"
staging="$(mktemp -d)"

echo "→ Copie des fichiers suivis par Git + public/build"
git ls-files -z --cached --others --exclude-standard \
    | grep -zv -E '^(tests/|scripts/|node_modules/|\.env$)' \
    | xargs -0 -I{} cp --parents "{}" "$staging"
cp -r public/build "$staging/public/"

echo "→ Création de $release"
tar -czf "$release" -C "$staging" .
rm -rf "$staging"

echo "Archive prête : $release (vendor/ non inclus : composer install sur le serveur)"
