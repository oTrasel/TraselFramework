<?php

use Helpers\Core\Routes;

Routes::group([
    'prefix' => '/home',
    'middleware' => ['ExampleMiddleware']
], function () {
    Routes::get('example', 'HomeController@index', ['TestMiddleware']);
});

Routes::get('contact', 'ContactController@index');