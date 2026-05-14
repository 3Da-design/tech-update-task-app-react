#!/bin/sh
set -e

if [ ! -f vendor/autoload.php ]; then
  echo "docker-entrypoint: vendor/autoload.php not found; running composer install..."
  composer install --no-interaction --prefer-dist --no-progress
fi

exec docker-php-entrypoint "$@"
