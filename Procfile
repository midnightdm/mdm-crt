web: vendor/bin/heroku-php-apache2 
worker: while true; do php daemon/crtdaemon.php -d apc.enabled=0; sleep 1; done