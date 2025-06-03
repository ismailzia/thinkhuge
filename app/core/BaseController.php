<?php

namespace App\core;

class BaseController
{
    /**
     * Constructor.
     * Starts a session if none exists.
     */
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Redirect to a given URL and stop execution.
     *
     * @param string $url
     * @return void
     */
    protected function redirect($url)
    {
        header("Location: $url");
        exit;
    }

    /**
     * Render a view with optional parameters and layout.
     *
     * @param string $view   View name without '_view.php' suffix
     * @param array  $params Variables to extract into the view
     * @param string|null $layout Layout file name or null for no layout
     * @return void
     */
    protected function render($view, $params = [], $layout = 'main_layout_view.php')
    {
        extract($params);

        $viewFile = __DIR__ . '/../views/' . $view . '_view.php';

        // Capture view output
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Include layout or output content directly
        if ($layout !== null) {
            include __DIR__ . '/../views/' . $layout;
        } else {
            echo $content;
        }
    }
}
