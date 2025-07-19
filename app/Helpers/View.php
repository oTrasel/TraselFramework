<?php

namespace Helpers;

use Exception;

class View
{
    public static function render(string $template, array $data = []): void
    {
        $viewPath = __DIR__ . '/../views/' . $template . '.php';

        if (!file_exists($viewPath)) {
            throw new Exception("View '$template' not found at $viewPath");
        }

        extract($data);
        require $viewPath;
    }
}
