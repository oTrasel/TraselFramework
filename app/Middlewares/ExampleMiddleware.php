<?php

namespace Middlewares;

class ExampleMiddleware {

    public function handle($request){
        var_dump('ExampleMiddleware');
    }

};