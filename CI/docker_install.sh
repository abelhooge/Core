#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && [[ ! -e /.dockerinit ]] && exit 0

set -xe

# Install git (the php image doesn't have it) which is required by composer
apt-get update -yqq
apt-get install git zip unzip -yqq

# Install xdebug
pecl install xdebug
docker-php-ext-enable xdebug

# Install mysql driver
# Here you can install any other extension that you need
docker-php-ext-install pdo_mysql