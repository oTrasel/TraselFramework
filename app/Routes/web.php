<?php

use Helpers\Core\Routes;

Routes::group([
    'prefix' => 'home'
], function () {
    Routes::get('/', 'HomeController@index');
});

Routes::get('contact', 'ContactController@index');