<?php

use Helpers\Core\Routes;

Routes::post('contact', 'ContactController@store');
Routes::post('login', 'AuthController@login');