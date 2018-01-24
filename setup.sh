#!/usr/bin/env bash
docker run -p 9005:80 --name wmg-cms-client -v "$PWD":/var/www/html php:7.0-apache /bin/bash -c 'a2enmod rewrite; apache2-foreground'
docker stop wmg-cms-client
docker rm wmg-cms-client