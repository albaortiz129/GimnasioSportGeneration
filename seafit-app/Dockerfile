# Usar la imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalar dependencias del sistema, Node.js y npm (para Vite y dependencias frontend)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm

# Limpiar cache de apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP necesarias para Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Habilitar el módulo de reescritura de Apache para las rutas de Laravel
RUN a2enmod rewrite

# Cambiar el document root de Apache a la carpeta "public" de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar todo el código de la aplicación al contenedor
COPY . /var/www/html

# Instalar las dependencias de PHP de Laravel
# Usamos --no-dev y --optimize-autoloader para producción
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Instalar dependencias de Node.js y compilar los assets (Vite)
RUN npm ci
RUN npm run build

RUN mkdir -p /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/testing \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache

# Asignar los permisos correctos a las carpetas de Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Comando para ejecutar el servidor de Apache
CMD ["apache2-foreground"]
