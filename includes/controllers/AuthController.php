<?php
class AuthController extends BaseController {
    public function index() {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['admin_id'])) {
            $this->redirect('index.php?page=dashboard');
        }
        
        if ($this->isPost()) {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Validate input
            $errors = $this->validateRequired($_POST, ['username', 'password']);
            
            if (empty($errors)) {
                // Query for admin
                $stmt = $this->db->prepare("SELECT id, username, password FROM admins WHERE username = ?");
                $stmt->execute([$username]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($admin && password_verify($password, $admin['password'])) {
                    // Set session
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    
                    $this->setFlashMessage('success', 'Welcome back, ' . $admin['username'] . '!');
                    $this->redirect('index.php?page=dashboard');
                } else {
                    $errors['login'] = 'Invalid username or password';
                }
            }
            
            $this->data['errors'] = $errors;
        }
        
        $this->render('auth/login');
    }
    
    public function logout() {
        // Clear all session data
        session_destroy();
        
        // Redirect to login page
        $this->redirect('index.php?page=login');
    }
}
?> 