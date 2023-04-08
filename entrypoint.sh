#!/bin/bash

export DB_DATABASE=wordpress
export DB_USERNAME=wp_mail_catcher
export DB_PASSWORD=password
export PHP_VERSION=8.0
export WP_VERSION=6.2

CMD=$1
SECONDARY_CMD=$2

run_grunt() {
  docker-compose run --name grunt --rm grunt "$1"
}

run_composer() {
  docker-compose run --name composer --rm composer composer "$1"
}

if [ "$CMD" == "up" ]; then
  run_grunt compile
  run_composer install
  docker-compose up "$SECONDARY_CMD"
elif [ "$CMD" == "phpunit" ]; then
  docker-compose run --name phpunit --rm -w /var/www/html/wp-content/plugins/wp-mail-catcher wordpress ./vendor/bin/phpunit
elif [ "$CMD" == "grunt" ]; then
  run_grunt "$SECONDARY_CMD"
elif [ "$CMD" == "composer" ]; then
  run_composer "$SECONDARY_CMD"
fi
