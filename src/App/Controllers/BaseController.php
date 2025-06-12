<?php

namespace App\Controllers;

class BaseController {
    protected $db;
    protected $config;
    protected $viewData = [];

    public function __construct() {
        // Database connection and config would be initialized here
        // $this->db = new \App\Core\Database();
        // $this->config = \App\Core\Config::get();
        
        // Initialize common view data
        $this->viewData = [
            'app_name' => 'Water Sports Rental',
            'app_url' => 'http://localhost/pfeee',
            'current_year' => date('Y')
        ];
    }

    /**
     * Render a view with data
     */
    public function render($view, $data = []) {
        // Merge with default view data
        $data = array_merge($this->viewData, $data);
        
        // Extract variables for the view
        extract($data);
        
        $viewPath = "views/$view.php";
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View file not found: $viewPath");
        }
        
        // Start output buffering
        ob_start();
        include $viewPath;
        return ob_get_clean();
    }
    
    /**
     * Redirect to another page
     */
    public function redirect($url) {
        header("Location: $url");
        exit;
    }
    
    /**
     * Set a flash message to be displayed on the next page
     */
    public function setFlash($type, $message) {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Get flash messages and clear them
     */
    public function getFlash() {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }
    
    /**
     * Get input data from request
     */
    public function input($key = null, $default = null) {
        if ($this->isPost()) {
            $data = $_POST;
        } else {
            $data = $_GET;
        }
        
        if ($key === null) {
            return $data;
        }
        
        return $data[$key] ?? $default;
    }
    
    /**
     * Check if request is POST
     */
    public function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Check if request is GET
     */
    public function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Check if request is AJAX
     */
    public function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Return JSON response
     */
    public function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Validate input data
     */
    public function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $fieldRules = explode('|', $fieldRules);
            
            foreach ($fieldRules as $rule) {
                // Check if rule has parameters
                if (strpos($rule, ':') !== false) {
                    list($ruleName, $ruleParam) = explode(':', $rule, 2);
                } else {
                    $ruleName = $rule;
                    $ruleParam = null;
                }
                
                // Apply validation rules
                switch ($ruleName) {
                    case 'required':
                        if (!isset($data[$field]) || trim($data[$field]) === '') {
                            $errors[$field] = ucfirst($field) . ' field is required.';
                        }
                        break;
                        
                    case 'email':
                        if (isset($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                            $errors[$field] = ucfirst($field) . ' must be a valid email address.';
                        }
                        break;
                        
                    case 'min':
                        if (isset($data[$field]) && strlen($data[$field]) < $ruleParam) {
                            $errors[$field] = ucfirst($field) . ' must be at least ' . $ruleParam . ' characters.';
                        }
                        break;
                        
                    case 'max':
                        if (isset($data[$field]) && strlen($data[$field]) > $ruleParam) {
                            $errors[$field] = ucfirst($field) . ' must not exceed ' . $ruleParam . ' characters.';
                        }
                        break;
                        
                    case 'numeric':
                        if (isset($data[$field]) && !is_numeric($data[$field])) {
                            $errors[$field] = ucfirst($field) . ' must be a number.';
                        }
                        break;
                        
                    case 'date':
                        if (isset($data[$field])) {
                            $date = \DateTime::createFromFormat('Y-m-d', $data[$field]);
                            if (!$date || $date->format('Y-m-d') !== $data[$field]) {
                                $errors[$field] = ucfirst($field) . ' must be a valid date (YYYY-MM-DD).';
                            }
                        }
                        break;
                        
                    case 'unique':
                        // Check if value already exists in the database
                        // This would require database integration
                        break;
                }
                
                // Stop validating this field if we already have an error
                if (isset($errors[$field])) {
                    break;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Handle file upload
     */
    public function uploadFile($fileInput, $targetDir, $allowedTypes = ['jpg', 'jpeg', 'png'], $maxSize = 5242880) {
        if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => 'File upload failed or no file selected.'
            ];
        }
        
        $file = $_FILES[$fileInput];
        $fileName = basename($file['name']);
        $fileSize = $file['size'];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Generate a unique filename
        $newFileName = uniqid() . '.' . $fileType;
        $targetPath = $targetDir . '/' . $newFileName;
        
        // Validate file type
        if (!in_array($fileType, $allowedTypes)) {
            return [
                'success' => false,
                'error' => 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes)
            ];
        }
        
        // Validate file size
        if ($fileSize > $maxSize) {
            return [
                'success' => false,
                'error' => 'File size exceeds the limit of ' . ($maxSize / 1024 / 1024) . 'MB.'
            ];
        }
        
        // Create directory if it doesn't exist
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return [
                'success' => true,
                'filename' => $newFileName,
                'path' => $targetPath
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Failed to move uploaded file.'
            ];
        }
    }
} 