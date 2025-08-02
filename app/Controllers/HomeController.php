<?php

namespace Controllers;

use Helpers\Facilities\View;

class HomeController {

    public function index() 
    {
        $dados = [
            "titulo" => "Home Controller",
            "dados" => "Entrou na pagina HOME"
        ];

        View::render('home', $dados);
    }
}