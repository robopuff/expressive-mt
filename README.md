# MT Sample Zend Expressive

Just a sample of _Zend Expressive_ api. Middleware for the win! :) 

## How to run it

* `$ composer install`
* `$ cp config/autoload/local.php.dist config/autoload/local.php`
* Check values for mongo in `config/autoload/local.php`

To run a built-in:
* `$ php -S 0:8080 -t public/ public/index.php`

To run it in docker:
* `$ docker-compose up`

To run tests just use
* `$ composer check`

It will analyse code with `phpstan` and `phpcs` and after that it'll check for `phpunit` tests

For **e2e** tests use `newman`, a Postman CLI automation client
* To install it use: `$ npm install -g newman`
* To run tests use `$ newman run e2e/postman_collection.json -e e2e/postman_docker_env.json`

This code requires `php>=7.2` with `mongodb` and `bcmath` installed -
only used for display purposes, all monetary values are stored in cents

## List of things not finished

* Make `Items` endpoint - currently just a mock
* Add post body filters
* Add response body negotiation
* ACL and overall identification of a user

## API endpoints

* `[GET] /`
    - A home endpoint
* `[GET] /ping`
    - Just a ping message, useful to check for performance issues
* `[GET] /api/v1/cart`
    - A list of all carts, can filter via query params (like `?filters[status][]=created&filters[status][]=deleted`)
* `[GET] /api/v1/cart/[id]`
    - Details of specified cart, replace `[id]` with cart id
* `[POST] /api/v1/cart`
    - Create a new cart, can take a body with json `{"items":[{"id":1,"qty":1,"unit_price":123}]}`
* `[PATCH] /api/v1/cart/[id]`
    - Update list of items (same body as _POST_, for items already in cart price is not required)
    - if you want to remove an item just send `"qty":0` with it
* `[DELETE] /api/v1/cart/[id]`
    - Mark cart as deleted