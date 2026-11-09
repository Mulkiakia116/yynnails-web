FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
 && a2enmod rewrite \
 && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

COPY . /var/www/html/
# salinan foto bawaan repo, untuk dipulihkan ke volume
RUN cp -r /var/www/html/uploads /opt/uploads-seed

CMD rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
 ; a2enmod mpm_prefork \
 ; mkdir -p /var/www/html/uploads/galeri /var/www/html/uploads/layanan /var/www/html/uploads/referensi \
 ; cp -rn /opt/uploads-seed/. /var/www/html/uploads/ \
 ; chown -R www-data:www-data /var/www/html/uploads \
 ; sed -i "s/Listen 80/Listen ${PORT:-80}/" /etc/apache2/ports.conf \
 ; sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT:-80}>/" /etc/apache2/sites-available/000-default.conf \
 ; exec docker-php-entrypoint apache2-foreground
