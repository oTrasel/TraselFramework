<?php

namespace Controllers;

use Helpers\Facilities\View;

class ContactController
{
    public function index()
    {
        $dados = [
            "titulo" => "Contact Controller",
            "dados" => "Entrou na pagina CONTACT"
        ];

        View::render('contact', $dados);
    }
}
