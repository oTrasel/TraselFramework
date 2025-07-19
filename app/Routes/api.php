<?php

use Helpers\Routes;

Routes::post('/contact', 'ContactController@store');
Routes::post('/login', 'AuthController@login');