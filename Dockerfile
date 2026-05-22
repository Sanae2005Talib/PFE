FROM php:8.2-apache

# Supprimer directement le fichier de configuration de mpm_event pour de bon
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf \
    && a2enmod mpm_prefork

# Installer les extensions PDO et MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copier le code du projet
COPY . /var/www/html/

# Donner les permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80