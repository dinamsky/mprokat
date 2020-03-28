FROM webdevops/php-nginx:7.3
LABEL maintainer="ole.larssen@gmail.com"
RUN echo "Install php extensions" \
    && apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y software-properties-common ca-certificates wget gnupg apt-transport-https git unzip\
    && wget -q https://packages.sury.org/php/apt.gpg -O- | apt-key add - \
    && echo "deb https://packages.sury.org/php/ stretch main" | tee /etc/apt/sources.list.d/php.list \
    && apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    php7.3-cli php7.3-common php7.3-curl php7.3-fpm php7.3-gd php7.3-imap php7.3-json php7.3-mbstring \
    php7.3-mysql php7.3-opcache php7.3-readline php7.3-xml php7.3-xmlrpc php7.3-zip \
    && echo "Install composer and npm..." \
    && php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

WORKDIR /app
COPY . /app
COPY ./deploy/unitd.config.json /var/lib/unit/conf.json
COPY ./docker-entrypoint.sh /usr/local/bin/

RUN cd /app/ && composer install --no-scripts \
    && DEBIAN_FRONTEND=noninteractive apt-get remove --purge --auto-remove -y  wget gnupg  && rm -rf /var/lib/apt/lists/*
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["unitd", "--no-daemon", "--control", "0.0.0.0:8080"]


