FROM php:8.2-apache

# Cài đặt extension PDO MySQL cho PHP
RUN docker-php-ext-install pdo pdo_mysql

# Bat module rewrite va thiet lap UTF-8 mac dinh cho Apache
RUN a2enmod rewrite \
    && echo "AddDefaultCharset UTF-8" >> /etc/apache2/conf-available/charset.conf \
    && a2enconf charset

# Sao chép toàn bộ mã nguồn vào thư mục root của web server
COPY . /var/www/html/

# Thiết lập quyền sở hữu thư mục cho Apache
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/
