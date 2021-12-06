# imagen de dockerhub que descargara
FROM php:8.0.13-fpm-buster

# algunas configuraciones para que funcione el contenedor
RUN docker-php-ext-install pdo pdo_mysql \
    && pecl install -o -f redis \
    && docker-php-ext-enable redis 

# instala composer en el contenedor
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

#start XDEBUG
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
RUN echo "xdebug.mode = debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
  && echo "xdebug.start_with_request = yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
  && echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
  && echo "xdebug.client_port = 9090" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
  && echo "xdebug.idekey = VSCODE" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
#end XDEBUG

# da permisos para editar los archivos en esta ruta del container
RUN chown -R www-data:www-data /var/www
RUN chmod 755 /var/www
