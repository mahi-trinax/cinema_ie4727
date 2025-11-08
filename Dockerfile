# use official php apache image
FROM php:8.2-apache

# install required php extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# enable apache rewrite module
RUN a2enmod rewrite

# copy project files to apache web root
COPY . /var/www/html/

#working directory
WORKDIR /var/www/html/

#permissions for uploads folder
RUN chmod -R 755 /var/www/html/uploads

#expose port 80
EXPOSE 80
