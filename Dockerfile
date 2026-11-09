FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
 && a2dismod -f mpm_event mpm_worker || true \
 && a2enmod mpm_prefork rewrite \
 && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Port diambil dari variabel PORT milik Railway (default 80 kalau dijalankan lokal)
ENV PORT=80
RUN sed -i 's/^Listen 80$/Listen ${PORT}/' /etc/apache2/ports.conf \
 && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/' /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html/

# Batas upload PHP bawaan cuma 2 MB, dinaikkan agar foto HP bisa masuk
RUN printf "upload_max_filesize=6M\npost_max_size=8M\n" > /usr/local/etc/php/conf.d/uploads.ini \
 && mkdir -p /var/www/html/uploads/referensi /var/www/html/uploads/galeri /var/www/html/uploads/layanan \
 && chown -R www-data:www-data /var/www/html/uploads

# Pastikan hanya satu MPM aktif setiap container start, lalu jalankan Apache
CMD mkdir -p /var/www/html/uploads/galeri /var/www/html/uploads/layanan /var/www/html/uploads/referensi \
 && chown -R www-data:www-data /var/www/html/uploads \
 && sed -i "s/80/${PORT:-80}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf \
 && docker-php-entrypoint apache2-foreground
