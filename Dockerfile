FROM php:8.2-apache


RUN a2enmod rewrite


WORKDIR /var/www/html


COPY . .


RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf


EXPOSE 80
CMD ["apache2-foreground"]
