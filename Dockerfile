FROM php:8.3.36-apache

RUN a2enmod rewrite
RUN printf 'display_errors=Off\nlog_errors=On\nexpose_php=Off\n' > /usr/local/etc/php/conf.d/zz-security.ini