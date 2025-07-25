#!/bin/bash




# Limpiar caché de Laravel

echo "Limpiando caché de Laravel..."

php artisan cache:clear

php artisan view:clear

php artisan config:clear

php artisan route:clear

php artisan event:clear




# Optimizar Laravel

echo "Optimizando aplicación..."

php artisan config:cache

php artisan route:cache

php artisan view:cache

php artisan event:cache


echo "¡Proceso completado! Laravel limpio, optimizado y Apache reiniciado."