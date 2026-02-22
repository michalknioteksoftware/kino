FROM php:8.4-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libxml2-dev \
    libicu-dev \
    libsqlite3-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# Install extensions (single-threaded to avoid parallel build failures)
RUN docker-php-ext-install pdo_mysql pdo_sqlite zip
RUN docker-php-ext-configure intl && docker-php-ext-install intl
RUN docker-php-ext-install opcache bcmath mbstring xml

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /app

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
