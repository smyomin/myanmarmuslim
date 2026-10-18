<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    // Rate limit login attempts
    if (!checkRateLimit('admin_login', 5, 15)) {
        $error = 'Too many login attempts. Please wait 15 minutes and try again.';
    } else {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Myanmar Muslims</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#1a6b3c', dark: '#0f4a28' },
                        gold: { DEFAULT: '#c9a84c' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-md">

        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-mosque text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-primary">Myanmar Muslims</h1>
            <p class="text-gray-500 text-sm">Admin Panel</p>
        </div>

        <!-- Error -->
        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
            <i class="fas fa-exclamation-circle mr-2"></i><?= clean($error) ?>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST">
             <?php csrfField(); ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" name="username"
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                           placeholder="Enter username" required>
                </div>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-3 text-gray-400"></i>
                    <input type="password" name="password"
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                           placeholder="Enter password" required>
                </div>
            </div>
            <button type="submit"
                    class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-lg transition">
                <i class="fas fa-sign-in-alt mr-2"></i>Login to Admin Panel
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="<?= SITE_URL ?>" class="text-sm text-gray-400 hover:text-primary">
                <i class="fas fa-arrow-left mr-1"></i>Back to Website
            </a>
        </div>
    </div>
</body>
</html>