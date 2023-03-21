# imagen de dockerhub que descargara
FROM php:8.0.13-fpm-buster

# Arguments defined in docker-compose.yml
ARG user
ARG uid

# algunas configuraciones para que funcione el contenedor
RUN apt-get update
RUN apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libmcrypt-dev libpng-dev libpq-dev zlib1g-dev libxml2-dev libzip-dev graphviz libicu-dev
RUN docker-php-ext-install zip gd xml pdo pdo_mysql \
    && pecl install -o -f redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl
RUN apt-get purge -y --auto-remove

# instala composer en el contenedor
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user
# da permisos para editar los archivos en esta ruta del container
RUN chown -R $user:www-data /var/www
RUN chmod 755 /var/www

#start XDEBUG
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
RUN echo "xdebug.mode = develop,debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
  && echo "xdebug.start_with_request = yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
  && echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
  && echo "xdebug.client_port = 9090" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
  && echo "xdebug.discover_client_host = 1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
  && echo "xdebug.cli_color = 1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
  && echo "xdebug.idekey = VSCODE" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
#end XDEBUG

# Set working directory
WORKDIR /var/www/html

USER $user
