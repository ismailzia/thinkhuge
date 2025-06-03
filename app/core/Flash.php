<?php
namespace App\core;

class Flash
{
    /**
     * Set a flash message by key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get and remove a flash message by key.
     * Returns $default if key not set.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $val = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);
        return $val;
    }

    /**
     * Check if a flash message key exists.
     *
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Clear a flash message by key.
     *
     * @param string $key
     * @return void
     */
    public static function clear($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * Set multiple flash messages at once.
     *
     * @param array $data Key => value pairs
     * @return void
     */
    public static function setMany($data)
    {
        foreach ($data as $k => $v) {
            self::set($k, $v);
        }
    }

    /**
     * Display flash alerts for 'success' and 'errors'.
     * Escapes output for safety.
     *
     * @return void
     */
    public static function displayAlerts()
    {
        $success = self::get('success');
        $errors = self::get('errors');

        if (!empty($success)) {
            echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
        }

        if (!empty($errors)) {
            echo '<div class="alert alert-danger">';
            foreach ($errors as $field => $messages) {
                echo htmlspecialchars($field) . ': ';
                echo htmlspecialchars(is_array($messages) ? implode(', ', $messages) : $messages);
                echo '<br>';
            }
            echo '</div>';
        }
    }
}
