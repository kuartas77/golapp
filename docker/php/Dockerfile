# Imagen base de PHP 8.3 FPM basada en Debian
FROM php:8.3-fpm

# Arguments defined in docker-compose.yml
ARG user=skidrunk77
ARG uid=1000

# Actualizar e instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libpq-dev \
    libzip-dev \
    libxml2-dev \
    libicu-dev \
    libonig-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libwebp-dev \
    libxpm-dev \
    graphviz \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

# Instalar mcrypt desde PECL (no está incluido por defecto en PHP 8.3)
# RUN pecl install mcrypt-1.0.7 \
#     && docker-php-ext-enable mcrypt

# Configurar e instalar extensiones de PHP
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
        --with-xpm \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install \
        zip \
        xml \
        intl \
        mbstring \
        exif \
        bcmath \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        sockets \
        opcache

# Instalar Redis desde PECL
# RUN pecl install redis \
#     && docker-php-ext-enable redis

# Instalar y configurar Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode = debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log_level=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=trigger" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.discover_client_host=false" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.cli_color=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Configurar PHP para Laravel
RUN echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/laravel.ini \
    && echo "upload_max_filesize = 100M" >> /usr/local/etc/php/conf.d/laravel.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/laravel.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/laravel.ini \
    && echo "date.timezone = UTC" >> /usr/local/etc/php/conf.d/laravel.ini

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario del sistema para ejecutar Composer y comandos Artisan
RUN useradd -G www-data,root -u $uid -d /home/$user $user \
    && mkdir -p /home/$user/.composer \
    && chown -R $user:$user /home/$user

# Configurar permisos
RUN chown -R $user:www-data /var/www
RUN chmod 755 /var/www

# Establecer directorio de trabajo
WORKDIR /var/www/html

USER $user

# Exponer el puerto de PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]