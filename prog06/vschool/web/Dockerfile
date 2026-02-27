FROM php:8.3-fpm

WORKDIR /var/www/html

RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
		git \
		curl \
		unzip \
		libzip-dev \
		libonig-dev \
		libxml2-dev \
	&& docker-php-ext-install pdo_mysql mbstring zip \
	&& apt-get clean \
	&& rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

EXPOSE 9000

CMD ["php-fpm"]
