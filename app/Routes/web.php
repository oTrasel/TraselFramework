<?php

use Helpers\Core\Routes;

Routes::group([
    'prefix' => 'home',
    'middleware' => ['auth']
], function () {
    Routes::get('/', 'HomeController@index', ['teste']);
});

Routes::get('contact', 'ContactController@index');