#!/bin/bash

export DB_DATABASE=wordpress
export DB_USERNAME=wp_mail_catcher
export DB_PASSWORD=password
export PHP_VERSION=8.0
export WP_VERSION=latest

CMD=$1

run_grunt() {
  docker-compose run --name grunt --rm "$@"
}

run_composer() {
  docker-compose run --name composer --rm composer "$@"
}

if [ "$CMD" == "up" ]; then
  run_grunt grunt compile
  run_composer composer install
  docker-compose "$@"
elif [ "$CMD" == "phpunit" ]; then
  docker-compose run --name phpunit --rm -w /var/www/html/wp-content/plugins/wp-mail-catcher wordpress ./vendor/bin/phpunit
elif [ "$CMD" == "grunt" ]; then
  run_grunt "$@"
elif [ "$CMD" == "composer" ]; then
  run_composer "$@"
elif [ "$CMD" == "phpstan" ]; then
  docker-compose run --name phpstan --rm -w /var/www/html/wp-content/plugins/wp-mail-catcher wordpress ./vendor/bin/phpstan analyze
elif [ "$CMD" == "phpcs" ]; then
  docker-compose run --name phpcs --rm -w /var/www/html/wp-content/plugins/wp-mail-catcher wordpress ./vendor/bin/phpcs
fi
