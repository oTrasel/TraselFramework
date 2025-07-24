<?php

namespace Helpers;

use Exception;

/**
 * Class View
 *
 * Handles rendering of view templates.
 *
 * @package Helpers
 */
class View
{
    /**
     * Render a view template with optional data.
     *
     * @param string $template The name of the view template (without .php extension).
     * @param array $data Associative array of data to extract into the view.
     * @throws Exception If the view file does not exist.
     */
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
