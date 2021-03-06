FROM php:7.1-fpm

WORKDIR /srv/hermod

# Install pdo extension for Postgre
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -yq \
    libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get autoremove -y

# Install opcache extension for PHP accelerator
RUN docker-php-ext-install opcache \
    && docker-php-ext-enable opcache \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get autoremove -y

# Install internationalization extension
RUN BUILD_DEPS="g++ libicu-dev" && \
    apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -yq \
    $BUILD_DEPS \
    libicu52 \
	&& docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false -o APT::AutoRemove::SuggestsImportant=false $BUILD_DEPS \
    && rm -rf /var/lib/apt/lists/*

# Install cgi-fcgi for healthcheck
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -yq \
    libfcgi0ldbl \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get autoremove -y

RUN echo 'date.timezone = "UTC"' >> /usr/local/etc/php/php.ini
RUN echo 'opcache.max_accelerated_files = 20000' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

COPY . /srv/hermod/
COPY docker/php/www.conf  /usr/local/etc/php-fpm.d/www.conf

RUN useradd --home-dir /srv/hermod/ hermod
RUN chown -R hermod:hermod /srv/hermod/
RUN chown -R www-data:www-data var/cache var/logs var/sessions

USER www-data

CMD ["/srv/hermod/docker/php/run.sh"]

HEALTHCHECK --interval=5s --timeout=3s \
  CMD SCRIPT_NAME=/ping \
    SCRIPT_FILENAME=/ping \
    REQUEST_METHOD=GET \
    cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -c pong || exit 1
