<?php

use Helpers\Routes;

Routes::get('/', 'HomeController@index');
Routes::get('/contact', 'ContactController@index');
