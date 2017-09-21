# Meanbee_PWA

[Progressive Web App](https://developers.google.com/web/progressive-web-apps/) helpers for Magento 2.

## Installation

Install this extension via Composer:

    composer config repositories.meanbee-pwa vcs https://github.com/meanbee/magento2-pwa
    composer require meanbee/magento2-pwa

## Development

### Setting up a development environment

A Docker development environment is included with the project:

    composer run-script --timeout 0 dev-install \
    composer run-script dev-start
