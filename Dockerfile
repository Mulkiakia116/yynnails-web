FROM php:8.3-apache

RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
 && a2enmod mpm_prefork rewrite \
 && docker-php-ext-install pdo_mysql \
 && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Default PHP hanya mengizinkan upload 2 MB, padahal foto HP biasanya 3-10 MB.
# Batas dinaikkan supaya sesuai dengan batas 5 MB di actions/proses_booking.php.
RUN { \
    echo "file_uploads = On"; \
    echo "upload_max_filesize = 6M"; \
    echo "post_max_size = 8M"; \
    echo "memory_limit = 128M"; \
    echo "max_file_uploads = 5"; \
 } > /usr/local/etc/php/conf.d/uploads.ini

COPY . /var/www/html/

# Apache berjalan sebagai www-data, jadi folder upload harus miliknya agar bisa ditulis
RUN mkdir -p /var/www/html/uploads/referensi /var/www/html/uploads/galeri /var/www/html/uploads/layanan \
 && chown -R www-data:www-data /var/www/html/uploads \
 && chmod -R 775 /var/www/html/uploads

CMD sed -i "s/80/${PORT:-80}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf && docker-php-entrypoint apache2-foreground
