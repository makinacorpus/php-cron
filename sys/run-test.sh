#!/bin/bash

echo "Environment setup"
if [ ! -e "./composer.phar" ]; then
    wget https://getcomposer.org/download/2.5.5/composer.phar -O ./composer.phar
    chmod +x ./composer.phar
fi
# docker-compose -p crontest build --force-rm --pull
docker-compose -p crontest build

echo "Running test on PHP 8.0"
docker-compose -p crontest run php80 /var/code/run-internal.sh

echo "Running test on PHP 8.1"
docker-compose -p crontest run php81 /var/code/run-internal.sh

echo "Running test on PHP 8.2"
docker-compose -p crontest run php82 /var/code/run-internal.sh
