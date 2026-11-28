FROM php:8.2-apache

# Cài đặt extension PDO MySQL cho PHP
RUN docker-php-ext-install pdo pdo_mysql

# Cấu hình Apache lắng nghe port 8080 (khớp với internal_port 8080 trên Fly.io / Cloud Run)
ENV PORT=8080
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Bật module rewrite, cho phép .htaccess ghi đè (AllowOverride All) và thiết lập UTF-8 mặc định cho Apache
RUN a2enmod rewrite \
    && echo "AddDefaultCharset UTF-8" >> /etc/apache2/conf-available/charset.conf \
    && a2enconf charset \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Sao chép toàn bộ mã nguồn vào thư mục root của web server
COPY . /var/www/html/

# Thiết lập quyền sở hữu thư mục cho Apache
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/

EXPOSE 8080
