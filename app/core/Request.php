<?php
namespace App\core;

class Request
{
    /**
     * Get the HTTP method for the request.
     *
     * @return string
     */
    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get a sanitized value from $_GET or all values if no key provided.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return $this->fetch($_GET, $key, $default);
    }

    /**
     * Get a sanitized value from $_POST or all values if no key provided.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function post($key = null, $default = null)
    {
        return $this->fetch($_POST, $key, $default);
    }

    /**
     * Get all input data based on HTTP method.
     * Supports GET, POST, and JSON input for PUT/PATCH/DELETE.
     *
     * @return array
     */
    public function all()
    {
        $data = [];
        if ($this->method() === 'GET') {
            $data = $_GET;
        } elseif ($this->method() === 'POST') {
            $data = $_POST;
        } elseif (in_array($this->method(), ['PUT', 'PATCH', 'DELETE'])) {
            // Read JSON body for APIs
            $json = file_get_contents('php://input');
            $data = json_decode($json, true) ?: [];
        }
        return $this->sanitize($data);
    }

    /**
     * Get a sanitized single input value or default.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function input($key, $default = null)
    {
        $data = $this->all();
        return isset($data[$key]) ? $this->sanitize($data[$key]) : $default;
    }

    /**
     * Helper to fetch and sanitize data from array.
     *
     * @param array $array
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    private function fetch($array, $key, $default)
    {
        if ($key === null) {
            return $this->sanitize($array);
        }
        return isset($array[$key]) ? $this->sanitize($array[$key]) : $default;
    }

    /**
     * Recursively sanitize a value or array of values.
     * Currently trims and applies htmlspecialchars.
     *
     * @param mixed $value
     * @return mixed
     */
    private function sanitize($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate input data against given rules.
     * Supports modes: 'exception' (throws on error), 'api' (outputs JSON error), 'return' (returns errors).
     *
     * @param array $rules Key => rules string (e.g., 'required|email|min:5')
     * @param string $mode
     * @return array|void Validated data or exits/throws on error
     * @throws \Exception
     */
    public function validate($rules, $mode = 'exception')
    {
        $data = $this->all();
        $errors = [];
        $validated = [];
        $isInteger = [];

        foreach ($rules as $field => $ruleStr) {
            $value = $data[$field] ?? null;
            $rulesArr = explode('|', $ruleStr);

            $fieldIsInteger = in_array('integer', $rulesArr, true);
            $fieldIsNumeric = in_array('numeric', $rulesArr, true);
            $isInteger[$field] = $fieldIsInteger;

            $defaultValue = null;
            foreach ($rulesArr as $r) {
                if (strpos($r, 'default:') === 0) {
                    $defaultValue = substr($r, 8);
                    if ($fieldIsInteger && $defaultValue !== null) {
                        $defaultValue = (int) $defaultValue;
                    }
                }
            }

            if ((is_null($value) || $value === '') && !in_array('required', $rulesArr, true) && $defaultValue !== null) {
                $value = $defaultValue;
            }

            foreach ($rulesArr as $rule) {
                $params = [];
                if (strpos($rule, ':') !== false) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                if ($rule === 'required' && (is_null($value) || $value === '')) {
                    $errors[$field][] = "The $field field is required.";
                }

                if ($rule === 'nullable' && ($value === null || $value === '')) {
                    continue 2;
                }

                if ($rule === 'numeric' && !is_numeric($value)) {
                    $errors[$field][] = "Must be a numeric value.";
                }

                if ($rule === 'integer' && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $errors[$field][] = "Must be an integer.";
                }

                if ($rule === 'min') {
                    if ($fieldIsInteger || $fieldIsNumeric) {
                        if ($value !== '' && $value !== null && $value < $params[0]) {
                            $errors[$field][] = "Must be at least {$params[0]}.";
                        }
                    } else {
                        if (mb_strlen($value) < (int) $params[0]) {
                            $errors[$field][] = "Minimum {$params[0]} characters.";
                        }
                    }
                }

                if ($rule === 'max') {
                    if ($fieldIsInteger || $fieldIsNumeric) {
                        if ($value !== '' && $value !== null && $value > $params[0]) {
                            $errors[$field][] = "Must be at most {$params[0]}.";
                        }
                    } else {
                        if (mb_strlen($value) > (int) $params[0]) {
                            $errors[$field][] = "Maximum {$params[0]} characters.";
                        }
                    }
                }

                if ($rule === 'in' && !in_array($value, $params)) {
                    $allowed = implode(', ', $params);
                    $errors[$field][] = "Value must be one of: $allowed.";
                }

                if ($rule === 'regex') {
                    $pattern = $paramStr;
                    $pattern = trim($pattern);
                    $delimiter = substr($pattern, 0, 1);
                    $last = strrpos($pattern, $delimiter);
                    if ($last === false || $last <= 0 || $last != strlen($pattern) - 1) {
                        $errors[$field][] = "Invalid regex pattern for $field.";
                    } elseif (!preg_match($pattern, $value)) {
                        $errors[$field][] = "Invalid format for $field.";
                    }
                    continue;
                }

                if ($rule === 'confirmed') {
                    $confirmationField = $field . '_confirmation';
                    $confirmationValue = $data[$confirmationField] ?? null;
                    if ($value !== $confirmationValue) {
                        $errors[$field][] = "The $field confirmation does not match.";
                    }
                }

                if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "Invalid email address.";
                }
            }

            if ($fieldIsInteger && $value !== null && $value !== '') {
                $value = (int) $value;
            } elseif ($fieldIsNumeric && $value !== null && $value !== '') {
                $value = (float) $value;
            }

            $validated[$field] = $value;
        }

        $this->validationErrors = $errors;
        $this->validatedData = $validated;

        if ($mode === 'api') {
            if (!empty($errors)) {
                http_response_code(422);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $errors,
                    'error_code' => 422
                ]);
                exit;
            }
            return $validated;
        }

        if ($mode === 'return') {
            return [
                'validated' => $validated,
                'errors' => $errors
            ];
        }

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors, JSON_PRETTY_PRINT));
        }

        return $validated;
    }

    /**
     * Get validation errors.
     *
     * @return array
     */
    public function errors()
    {
        return $this->validationErrors ?? [];
    }

    /**
     * Get validated data.
     *
     * @return array
     */
    public function validated()
    {
        return $this->validatedData ?? [];
    }

    /**
     * Check if validation passed.
     *
     * @return bool
     */
    public function passes()
    {
        return empty($this->validationErrors);
    }

    /**
     * Check if validation failed.
     *
     * @return bool
     */
    public function fails()
    {
        return !empty($this->validationErrors);
    }
}
