<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
require_once 'functions.php';

// Redirect if already logged in
if (isset($_SESSION['logged_in'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'admin';
        
        header("Location: dashboard.php");
        exit();
    }
    $error = "Invalid username or password";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3249d1;
            --secondary: #4cc9f0;
            --success: #4cc9a0;
            --warning: #f7b731;
            --danger: #f72585;
            --light: #f8f9fa;
            --dark: #1e1e1e;
            --gray: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .login-header {
            background: var(--light);
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .login-header h1 {
            color: var(--dark);
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--gray);
            font-size: 0.875rem;
        }

        .login-form {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-login:hover {
            background: var(--primary-dark);
        }

        .error-message {
            background: #fee2e2;
            color: var(--danger);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        @media (max-width: 480px) {
            .login-container {
                border-radius: 0;
            }
            
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><?= SITE_NAME ?></h1>
            <p>Please sign in to continue</p>
        </div>
        
        <div class="login-form">
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" 
                               id="username"
                               name="username" 
                               class="form-control" 
                               placeholder="Enter your username"
                               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               id="password"
                               name="password" 
                               class="form-control" 
                               placeholder="Enter your password"
                               required>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    Sign In <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </form>
        </div>
    </div>
</body>
</html>