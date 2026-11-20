#!/bin/sh
set -e

# Render fournit PORT (10000 par défaut pour les services Docker).
: "${PORT:=10000}"
export PORT

# Nginx ne sait pas lire les variables d'environnement : on génère la
# configuration à partir du modèle à chaque démarrage du conteneur.
envsubst '${PORT}' \
    < /etc/nginx/templates/default.conf.template \
    > /etc/nginx/sites-available/default

cd /var/www/html

# Le disque de Render Free est éphémère : les dossiers applicatifs doivent
# être présents à chaque redémarrage.
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Les migrations sont non destructives. On ne les lance que si l'option est
# explicitement activée dans les variables d'environnement Render.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Application des migrations..."
    php artisan migrate --force --no-interaction
fi

echo "Démarrage de PHP-FPM et Nginx sur le port ${PORT}"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/srsp.conf
