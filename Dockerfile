FROM php:8.2-apache

# Instalē nepieciešamās PHP paplašinājumu bibliotēkas (piemēram, pdo_mysql)
RUN docker-php-ext-install pdo pdo_mysql

# Nokopē visus projekta failus uz Apache servera mapi
COPY . /var/www/html/

# Atļauj Apache serverim rakstīt un lasīt failus
RUN chown -R www-data:www-data /var/www/html