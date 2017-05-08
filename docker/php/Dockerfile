FROM php:7.1-cli

WORKDIR /opt/project

VOLUME /opt/project

RUN apt-get update &&\
    apt-get install curl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

ENTRYPOINT ["php"]