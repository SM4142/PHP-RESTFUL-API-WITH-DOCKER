FROM php:8.2-apache

RUN apt-get update -y && apt-get install -y \
    libicu-dev \
    libmariadb-dev \
    libpq-dev \
    libsqlite3-dev \
    unzip zip \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install gettext intl pdo_mysql pdo_pgsql pdo_sqlite gd

RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd
RUN a2enmod rewrite


WORKDIR /var/www/html


COPY . .


RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf


EXPOSE 80
CMD ["apache2-foreground"]
