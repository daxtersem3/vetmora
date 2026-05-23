# 1. Usar la imagen oficial de PHP con Apache optimizada
FROM php:8.2-apache

# 2. Instalar dependencias del sistema y extensiones requeridas por Laravel y Filament
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql zip intl opcache bcmath exif \
    && a2enmod rewrite

# 3. Instalar Node.js y NPM para compilar los assets de Vite
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

# 4. Configurar el puerto para Hugging Face (Exige el 7860)
ENV PORT=7860
RUN sed -i 's/80/7860/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 5. Configurar Apache para correr como el usuario 1000 (Hugging Face ejecuta el contenedor con UID 1000)
RUN useradd -m -u 1000 user && \
    chown -R user:user /var/www/html /var/log/apache2 /var/run/apache2 /var/lock/apache2 /var/lib/apache2 /etc/apache2 && \
    sed -i 's/export APACHE_RUN_USER=www-data/export APACHE_RUN_USER=user/g' /etc/apache2/envvars && \
    sed -i 's/export APACHE_RUN_GROUP=www-data/export APACHE_RUN_GROUP=user/g' /etc/apache2/envvars

# 6. Establecer el directorio de trabajo
WORKDIR /var/www/html

# 7. Copiar todo el código del proyecto al contenedor asignando propiedad al usuario
COPY --chown=user:user . .

# 8. Copiar Composer oficial al contenedor
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 9. Cambiar al usuario no raíz (user)
USER user

# 10. Instalar las dependencias de Composer (producción)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 11. Instalar dependencias de Node y compilar assets (Vite)
RUN npm install && npm run build

# 12. Crear el enlace simbólico de almacenamiento de Laravel y optimizar la app
RUN php artisan storage:link && php artisan optimize

# 13. Comando de arranque: Ejecutar migraciones en caliente y encender Apache
CMD php artisan migrate --force && apache2-foreground
