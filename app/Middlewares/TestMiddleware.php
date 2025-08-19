<?php

namespace Middlewares;

class TestMiddleware {

    public function handle($request){
        var_dump('TestMiddleware');
    }

};