<?php

use Helpers\Core\Routes;

Routes::get('/', 'HomeController@index');
Routes::get('contact', 'ContactController@index');
