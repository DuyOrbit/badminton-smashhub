FROM php:8.2-apache

# Cài đặt extension PDO MySQL cho PHP
RUN docker-php-ext-install pdo pdo_mysql

# Bật module rewrite của Apache (cần thiết cho các URL thân thiện nếu có)
RUN a2enmod rewrite

# Sao chép toàn bộ mã nguồn vào thư mục root của web server
COPY . /var/www/html/

# Thiết lập quyền sở hữu thư mục cho Apache
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/
