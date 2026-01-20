#!/bin/sh
set -e

# Esperar a que la base de datos esté disponible (simple retry)
if [ -n "$DATABASE_URL" ]; then
  echo "Esperando base de datos..."
  for i in $(seq 1 30); do
    php -r "new PDO(getenv('DATABASE_URL'));" >/dev/null 2>&1 && break
    echo "Intento $i/30: DB no lista aún"
    sleep 2
  done
fi

# Migraciones
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || exit 1

# Seeder admin
php bin/console app:seed-admin-user || true

# Seeder datos demo (roles, docente, estudiante, padre, curso, matrícula, calificaciones, chat)
php bin/console app:seed-demo-data || true

# Arrancar PHP-FPM
exec php-fpm
