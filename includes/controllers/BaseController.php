<?php
class BaseController {
    protected $db;
    protected $data = [];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    protected function render($template, $data = []) {
        // Merge with any existing data
        $this->data = array_merge($this->data, $data);
        
        // Extract data to make variables available in template
        extract($this->data);
        
        // Start output buffering
        ob_start();
        
        // Include header
        require_once BASE_PATH . '/templates/layout/header.php';
        
        // Include the main template
        require_once BASE_PATH . '/templates/' . $template . '.php';
        
        // Include footer
        require_once BASE_PATH . '/templates/layout/footer.php';
        
        // Get the contents and clean the buffer
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    protected function redirect($url) {
        header("Location: $url");
        exit;
    }

    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function getPostData() {
        return $_POST;
    }

    protected function validateRequired($data, $fields) {
        $errors = [];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        return $errors;
    }

    protected function setFlashMessage($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    protected function getFlashMessage() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
?> 