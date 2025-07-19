<?php

require("../vendor/autoload.php");

use Helpers\Routes;

class Index{

    public function __construct()
    {
        require 'Routes/web.php';
        require 'Routes/api.php';
    }

    private function setUri () {
        $_REQUEST["endpoint"] = str_replace("/TraselFramework/app", "", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        if (empty($_REQUEST["endpoint"])) {
            $_REQUEST["endpoint"] = "/";
        }
    }

    private function verifyMethod(){
        if (!isset($_REQUEST["method"]) || empty($_REQUEST["method"])) {
            $_REQUEST["method"] = "GET";
        }
    }

    private function verifyHandler(){
        if (!isset($_REQUEST["handler"]) || empty($_REQUEST["handler"])) {
            $_REQUEST["handler"] = "index";
        }
    }

    public function dispatch() {
        $this->setUri();
        $this->verifyMethod();
        $this->verifyHandler();

        try {
            Routes::dispatch($_REQUEST);
        } catch (Exception $e){
            var_dump($e->getMessage());
        }
    }

}

$index = new Index();

$index->dispatch();

?>