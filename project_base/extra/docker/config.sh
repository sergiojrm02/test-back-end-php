#!/bin/bash

# wait for mysql
#while ! mysqladmin ping -h"app-db-mysql" -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" --silent; do
#  sleep 1
#done

# run artisan scripts
pushd /var/www/html
  #composer install
  php artisan migrate:install
  php artisan migrate
  chown -R www-data:www-data /app/storage/
  #chown -R www-data:www-data /var/www/html
popd


