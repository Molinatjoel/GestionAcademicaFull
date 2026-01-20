#!/bin/sh
set -e
# Si node_modules está vacío, instala dependencias dentro del contenedor (volumen dedicado)
if [ ! -d node_modules ] || [ -z "$(ls -A node_modules 2>/dev/null)" ]; then
  echo "node_modules vacío, instalando dependencias..."
  npm install --legacy-peer-deps || npm install --legacy-peer-deps --force
fi
exec npm run dev -- --host 0.0.0.0 --port 5173
