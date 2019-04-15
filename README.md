# MT Sample Zend Expressive

## How to run it

* `$ composer install`

To run a built-in:
* `$ php -S 0:8080 -t public/ public/index.php`

To run it in docker:
* `$ docker-compose up`

To run tests just use
`$ composer check`
It will analyse code with `phpstan` and `phpcs` and after that it'll check for `phpunit` tests

For **e2e** tests use `newman`, a Postman CLI automation client
To install it use: `$ npm install -g newman`
To run tests use `$ newman run e2e/postman_collection.json -e e2e/postman_docker_env.json`

This code requires `php>=7.2` with `mongodb` and `bcmath` installed -
only used for display purposes, all monetary values are stored in cents

## List of things not finished

* Make `Items` endpoint - currently just a mock
* Add post body filters
* Add response body negotiation