<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Water Sports Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0099ff 0%, #0043a8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .forgot-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }
        .forgot-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .forgot-logo i {
            font-size: 3rem;
            color: #0043a8;
            margin-bottom: 1rem;
        }
        .forgot-logo h1 {
            color: #0043a8;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }
        .btn-primary {
            background: #0043a8;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            width: 100%;
            font-weight: 500;
        }
        .btn-primary:hover {
            background: #003585;
        }
        .alert {
            border-radius: 8px;
        }
        .form-floating label {
            padding-left: 1rem;
        }
        .form-floating>.form-control {
            padding-left: 1rem;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-logo">
            <i class="bi bi-key-fill"></i>
            <h1>Reset Password</h1>
            <p class="text-muted">Enter your email to receive reset instructions</p>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                <div class="alert alert-<?php echo $type === 'error' ? 'danger' : $type; ?> mb-3">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endforeach; ?>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <form action="index.php?page=auth/forgot-password" method="POST" class="needs-validation" novalidate>
            <div class="mb-4 form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email address" required>
                <label for="email">Email address</label>
                <div class="invalid-feedback">
                    Please enter a valid email address.
                </div>
            </div>
            <div class="mb-3">
                <button type="submit" class="btn btn-primary">
                    Send Reset Instructions <i class="bi bi-envelope ms-1"></i>
                </button>
            </div>
            <div class="text-center">
                <a href="index.php?page=login" class="text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Back to Login
                </a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html> 