# Meanbee_PWA

[Progressive Web App](https://developers.google.com/web/progressive-web-apps/) helpers for Magento 2.

## Installation

Install this extension via Composer:

    composer config repositories.meanbee-pwa vcs https://github.com/meanbee/magento2-pwa
    composer require meanbee/magento2-pwa

## Development

### Setting up a development environment

A Docker development environment is included with the project:

    mkdir magento
    docker-compose up -d db # Allow a few seconds for the db to initalise
    docker-compose run --rm cli bash /src/setup.sh
    docker-compose up -d
