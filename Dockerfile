FROM php:8.2-apache

# Fix strict pour l'erreur Apache MPM
RUN a2dismod mpm_event && a2enmod mpm_prefork

# Installer les extensions PDO et MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copier le code du projet
COPY . /var/www/html/

# Donner les permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80