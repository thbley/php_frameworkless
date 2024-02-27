<?php

namespace TaskService\Services;

use Exception;
use TaskService\Views\View;

class TemplateService
{
    public function escape(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES | ENT_HTML5);
    }

    public function render(View $view): string
    {
        /** @var string $template */
        $template = $view::TEMPLATE;

        if ($template === '') {
            throw new Exception('missing template');
        }

        if (!file_exists($template)) {
            throw new Exception('missing template file');
        }

        ob_start();

        require $template;

        return trim(ob_get_clean() ?: '');
    }
}
