#Dockerfile para el frontend
FROM node:20
# Fuerza uso de versión JS de rollup para evitar binarios nativos faltantes
ENV ROLLUP_FORCE_JS=1
ENV ROLLUP_SKIP_NODE_WARN=1
# Establecer el directorio de trabajo
WORKDIR /app
# copiar los archivos de dependencias
COPY package*.json ./
# instalar las dependencias (regenera lock en linux para evitar bug de rollup opcional)
RUN rm -f package-lock.json && npm install --legacy-peer-deps \
	&& npm install @rollup/rollup-linux-x64-gnu --no-save || true
# copiar el resto de los archivos de la aplicación
COPY . .
# Exponer el puerto 5173 y arrancar la aplicación
EXPOSE 5173
# Entrypoint para instalar deps en volumen y lanzar Vite
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh
ENTRYPOINT ["/docker-entrypoint.sh"]
