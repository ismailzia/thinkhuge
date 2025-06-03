<?php
/**
 * Retrieve application configuration or specific config value by dot notation key.
 *
 * @param string|null $key Configuration key like 'db.host' or null to get all config.
 * @return mixed|null Configuration value or null if key not found.
 */
function app_config($key = null)
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../../config/config.php';
    }
    if ($key === null)
        return $config;

    $parts = explode('.', $key);
    $value = $config;

    foreach ($parts as $part) {
        if (!isset($value[$part]))
            return null;
        $value = $value[$part];
    }
    return $value;
}

/**
 * Generate or get the CSRF token stored in session.
 *
 * @return string CSRF token
 */
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate a hidden HTML input field with CSRF token.
 *
 * @return string HTML string
 */
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Redirect back to the referring page or to a default if no valid referer.
 *
 * @param string $default Default path to redirect if no valid referer
 * @return never Exits script after redirect
 */
function redirect_back($default = '/dashboard'): never
{
    $referer = $_SERVER['HTTP_REFERER'] ?? $default;
    $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    if (strpos($referer, $baseUrl) === 0) {
        $referer = substr($referer, strlen($baseUrl));
    } else {
        $referer = $default;
    }
    header("Location: $referer");
    exit;
}

/**
 * Render a view file with optional parameters and layout.
 *
 * @param string $view View name without suffix
 * @param array  $params Variables to extract into view
 * @param string|null $layout Layout filename or null for no layout
 * @return void
 */
function render($view, $params = [], $layout = 'main_layout_view.php')
{
    extract($params);

    $viewFile = __DIR__ . '/../views/' . $view . '_view.php';
    ob_start();
    include $viewFile;
    $content = ob_get_clean();

    if ($layout !== null) {
        include __DIR__ . '/../views/' . $layout;
    } else {
        echo $content;
    }
}

/**
 * Get the base path of the application relative to web root.
 *
 * @return string Base path (e.g., "/myapp" or "")
 */
function app_base_path()
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    $basePath = dirname($scriptName);
    if ($basePath === '/' || $basePath === '\\') {
        $basePath = '';
    }
    return $basePath;
}
