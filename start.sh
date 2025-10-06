#!/bin/bash

# Start PHP-FPM
php-fpm8.2 -D

# Start nginx
nginx -g "daemon off;"