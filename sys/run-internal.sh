#!/bin/bash
./composer.phar install
vendor/bin/phpunit "$@"
