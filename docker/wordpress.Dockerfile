ARG PHP_VERSION
ARG WP_VERSION

FROM wordpress:${WP_VERSION}-php${PHP_VERSION}-apache

ARG DB_DATABASE
ARG DB_USERNAME
ARG DB_PASSWORD

# Install dependencies for WP unit tests
RUN apt-get update && \
    apt-get -y install subversion

COPY ./install-wp-tests.sh /tmp/install-wp-tests.sh

RUN chmod +x /tmp/install-wp-tests.sh

RUN [ "sh", "-c", " /tmp/install-wp-tests.sh ${DB_DATABASE}_testing ${DB_USERNAME} ${DB_PASSWORD} mysql_testing" ]