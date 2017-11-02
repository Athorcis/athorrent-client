#!/bin/bash

function installComposer {
    echo 
    echo "install composer"
    echo
    
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '070854512ef404f16bac87071a6db9fd9721da1684cd4589b1196c3faf71b9a2682e2311b36a5079825e155ac7ce150d') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
}

function randomString {
    cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1
}

if [ $# -lt 4 ]
then
    echo "usage: install.sh db-username db-password seedbox-username seedbox-password"
    exit 1
else
    DB_USERNAME=$1
    DB_PASSWORD=$2
    SEEDBOX_USERNAME=$3
    SEEDBOX_PASSWORD=$4
fi

PHP=$(type -p php)

if [ -z "$PHP" ]
then
    echo "php is required to install athorrent-frontend"
    exit 1
fi

COMPOSER=$(type -p composer)

if [ -z "$COMPOSER" ]
then
    if [ ! -f "composer.phar" ]
    then
        installComposer
    fi
    
    COMPOSER="php composer.phar"
fi

YARN=$(type -p yarn)

if [ -z "$YARN" ]
then
    echo "yarn is required to install athorrent-frontend"
    exit 1
fi

echo
echo "Install server dependencies"
echo

"$COMPOSER" install -o

echo
echo "Install client dependencies"
echo

"$YARN" install
"$YARN" run prod

echo
echo "Create database"
echo

mysql -h 127.0.0.1 -u $DB_USERNAME -p$DB_PASSWORD < utils/athorrent.sql

echo
echo "Create config file"
echo

echo "<?php

define('DEBUG', false);

define('DB_USERNAME', '$DB_USERNAME');
define('DB_PASSWORD', '$DB_PASSWORD');
define('DB_NAME', 'athorrent');

define('REMEMBER_ME_KEY', '$(randomString)');
    
define('CSRF_SALT','$(randomString)');

if (isset(\$_SERVER['HTTP_HOST'])) {
    define('STATIC_HOST', \$_SERVER['HTTP_HOST']);
}
" > config/env.php

echo
echo "Create user"
echo

"$PHP" utils/create-admin.php $SEEDBOX_USERNAME $SEEDBOX_PASSWORD
