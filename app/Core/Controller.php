<?php
/**
 * Base Controller Class
 * All controllers extend this class for common functionality
 */

namespace Lesarge\Core;

abstract class Controller
{
    protected array $data = [];

    /**
     * Render HTML view
     */
    protected function render(string $view, array $data = []): string
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data);
        
        ob_start();
        require dirname(__DIR__) . "/Views/{$view}.php";
        return ob_get_clean();
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $status = 200): array
    {
        return [
            'status' => $status,
            'data' => $data,
        ];
    }

    /**
     * Check user authorization
     */
    protected function authorize(string $capability): void
    {
        if (!current_user_can($capability)) {
            wp_die('Unauthorized access', 'Unauthorized', ['response' => 403]);
        }
    }

    /**
     * Validate request data
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = "Field {$field} is required";
            }
        }

        if (!empty($errors)) {
            wp_die(wp_json_encode($errors), 'Validation Error', ['response' => 422]);
        }

        return $data;
    }
}
