<?php
require_once 'auth.php';
require_once '../includes/functions.php';

$success = '';
$error   = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Prevent deleting yourself
    if ($id === (int)$_SESSION['admin_id']) {
        $error = 'You cannot delete your own account.';
    } else {
        $count = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
        if ($count <= 1) {
            $error = 'Cannot delete the last admin account.';
        } else {
            $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$id]);
            header('Location: admins.php?deleted=1');
            exit;
        }
    }
}

// Handle add new admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');

    if (!$username || !$email || !$password) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if username or email exists
        $check = $pdo->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        if ($check->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hash]);
            $success = 'New admin account created successfully!';
        }
    }
}

// Handle change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $id          = (int)$_POST['admin_id'];
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirm     = trim($_POST['confirm_password'] ?? '');

    if (!$newPassword) {
        $error = 'New password is required.';
    } elseif (strlen($newPassword) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($newPassword !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // If changing own password, verify current password
        if ($id === (int)$_SESSION['admin_id']) {
            $currentPassword = trim($_POST['current_password'] ?? '');
            $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
            $stmt->execute([$id]);
            $admin = $stmt->fetch();
            if (!password_verify($currentPassword, $admin['password'])) {
                $error = 'Current password is incorrect.';
            }
        }

        if (!$error) {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?")->execute([$hash, $id]);
            $success = 'Password updated successfully!';
        }
    }
}

$admins = $pdo->query("SELECT * FROM admins ORDER BY created_at ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#1a6b3c', dark: '#0f4a28' },
                        gold:    { DEFAULT: '#c9a84c' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">

<nav class="bg-primary text-white px-6 py-4 flex justify-between items-center shadow">
    <div class="flex items-center gap-3">
        <img src="<?= SITE_URL ?>/assets/images/logowhite.png" alt="Myanmar Muslim Logo" class="w-12 h-12 object-cover">
        <span class="font-bold text-lg">Myanmar Muslims Admin</span>
    </div>
    <div class="flex items-center gap-3">
        <a href="dashboard.php" class="text-green-200 hover:text-white text-sm">
            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
        </a>
        <a href="logout.php"
           class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg transition">
            <i class="fas fa-sign-out-alt mr-1"></i>Logout
        </a>
    </div>
</nav>

<div class="flex">
    <!-- Sidebar -->
    <aside class="w-64 min-h-screen bg-white shadow-md hidden md:block flex-shrink-0">
        <nav class="p-4 space-y-1">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-tachometer-alt w-5"></i>Dashboard
            </a>
            <a href="books.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-book w-5"></i>Manage Books
            </a>
            <a href="news.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-newspaper w-5"></i>Manage News
            </a>
            <a href="subscribers.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-envelope w-5"></i>Subscribers
            </a>
            <a href="prayer-times.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-mosque w-5"></i>Prayer Times
            </a>
            <a href="events.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-calendar w-5"></i>Islamic Events
            </a>
            <a href="announcements.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-bullhorn w-5"></i>Announcements
            </a>
            <a href="analytics.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-chart-bar w-5"></i>Analytics
            </a>
            <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-cog w-5"></i>Settings
            </a>
            <a href="admins.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-green-50 text-primary font-medium">
                <i class="fas fa-user-shield w-5"></i>Manage Admins
            </a>
        </nav>
    </aside>

    <main class="flex-1 p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-user-shield text-primary mr-2"></i>Manage Admins
        </h1>

        <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-5">
            <i class="fas fa-check-circle mr-2"></i><?= clean($success) ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-5">
            <i class="fas fa-exclamation-circle mr-2"></i><?= clean($error) ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-5">
            <i class="fas fa-check-circle mr-2"></i>Admin account deleted successfully.
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Admin List -->
            <div class="lg:col-span-2 space-y-4">
                <h2 class="font-bold text-gray-800">All Admin Accounts</h2>

                <?php foreach ($admins as $admin): ?>
                <?php $isMe = $admin['id'] == $_SESSION['admin_id']; ?>
                <div class="bg-white rounded-xl shadow-sm p-5 border <?= $isMe ? 'border-primary' : 'border-gray-100' ?>">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <!-- Avatar -->
                            <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
                                 style="background: <?= $isMe ? '#1a6b3c' : '#6b7280' ?>">
                                <i class="fas fa-user-shield text-white"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="font-bold text-gray-800"><?= clean($admin['username']) ?></p>
                                    <?php if ($isMe): ?>
                                    <span class="text-xs bg-primary text-white px-2 py-0.5 rounded-full">
                                        You
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-gray-500"><?= clean($admin['email']) ?></p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Joined <?= formatDate($admin['created_at']) ?>
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button onclick="showPasswordModal(<?= $admin['id'] ?>, '<?= clean($admin['username']) ?>', <?= $isMe ? 'true' : 'false' ?>)"
                                    class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-600 px-3 py-1.5 rounded-lg transition font-medium">
                                <i class="fas fa-key mr-1"></i>Change Password
                            </button>
                            <?php if (!$isMe): ?>
                            <a href="admins.php?delete=<?= $admin['id'] ?>"
                               onclick="return confirm('Delete admin account: <?= clean($admin['username']) ?>? This cannot be undone.')"
                               class="text-xs bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded-lg transition font-medium">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Add New Admin Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6 sticky top-6">
                    <h2 class="font-bold text-gray-800 mb-5">
                        <i class="fas fa-user-plus text-primary mr-2"></i>Add New Admin
                    </h2>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="add">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Username <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fas fa-user absolute left-3 top-3 text-gray-400 text-sm"></i>
                                <input type="text" name="username" required
                                       class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary"
                                       placeholder="Enter username">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-3 top-3 text-gray-400 text-sm"></i>
                                <input type="email" name="email" required
                                       class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary"
                                       placeholder="Enter email">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fas fa-lock absolute left-3 top-3 text-gray-400 text-sm"></i>
                                <input type="password" name="password" id="newPassword" required
                                       minlength="8"
                                       class="w-full pl-9 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary"
                                       placeholder="Min 8 characters">
                                <button type="button" onclick="togglePassword('newPassword', 'eyeNew')"
                                        class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye text-sm" id="eyeNew"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fas fa-lock absolute left-3 top-3 text-gray-400 text-sm"></i>
                                <input type="password" name="confirm_password" id="confirmPassword" required
                                       class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary"
                                       placeholder="Repeat password">
                            </div>
                        </div>

                        <!-- Password strength indicator -->
                        <div id="strengthBar" class="hidden">
                            <div class="flex gap-1 mb-1">
                                <div class="h-1.5 flex-1 rounded-full bg-gray-200" id="s1"></div>
                                <div class="h-1.5 flex-1 rounded-full bg-gray-200" id="s2"></div>
                                <div class="h-1.5 flex-1 rounded-full bg-gray-200" id="s3"></div>
                                <div class="h-1.5 flex-1 rounded-full bg-gray-200" id="s4"></div>
                            </div>
                            <p class="text-xs text-gray-400" id="strengthText"></p>
                        </div>

                        <button type="submit"
                                class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-lg transition text-sm">
                            <i class="fas fa-user-plus mr-2"></i>Create Admin Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Change Password Modal -->
<div id="passwordModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
        <div class="flex justify-between items-center mb-5">
            <h3 class="font-bold text-gray-800">
                <i class="fas fa-key text-primary mr-2"></i>
                Change Password — <span id="modalUsername" class="text-primary"></span>
            </h3>
            <button onclick="closeModal()"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">
                &times;
            </button>
        </div>

        <form method="POST" class="space-y-4" id="passwordForm">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="admin_id" id="modalAdminId">

            <!-- Current password (only for own account) -->
            <div id="currentPasswordField">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Current Password <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-3 text-gray-400 text-sm"></i>
                    <input type="password" name="current_password" id="currentPassword"
                           class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary"
                           placeholder="Enter current password">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    New Password <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-3 text-gray-400 text-sm"></i>
                    <input type="password" name="new_password" id="modalNewPassword"
                           minlength="8" required
                           class="w-full pl-9 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary"
                           placeholder="Min 8 characters">
                    <button type="button" onclick="togglePassword('modalNewPassword', 'eyeModal')"
                            class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye text-sm" id="eyeModal"></i>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Confirm New Password <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-3 text-gray-400 text-sm"></i>
                    <input type="password" name="confirm_password" id="modalConfirmPassword"
                           required
                           class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary"
                           placeholder="Repeat new password">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 bg-primary hover:bg-primary-dark text-white font-semibold py-2.5 rounded-lg transition text-sm">
                    <i class="fas fa-save mr-2"></i>Update Password
                </button>
                <button type="button" onclick="closeModal()"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 rounded-lg transition text-sm">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Show password change modal
function showPasswordModal(adminId, username, isMe) {
    document.getElementById('modalAdminId').value   = adminId;
    document.getElementById('modalUsername').textContent = username;
    document.getElementById('modalNewPassword').value    = '';
    document.getElementById('modalConfirmPassword').value = '';
    document.getElementById('currentPassword').value     = '';

    // Show current password field only for own account
    const currentField = document.getElementById('currentPasswordField');
    const currentInput = document.getElementById('currentPassword');
    if (isMe) {
        currentField.style.display = 'block';
        currentInput.required = true;
    } else {
        currentField.style.display = 'none';
        currentInput.required = false;
    }

    document.getElementById('passwordModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('passwordModal').classList.add('hidden');
}

// Close on backdrop click
document.getElementById('passwordModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Toggle password visibility
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash text-sm';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye text-sm';
    }
}

// Password strength checker
document.getElementById('newPassword').addEventListener('input', function() {
    const val      = this.value;
    const bar      = document.getElementById('strengthBar');
    const segments = ['s1','s2','s3','s4'];
    const text     = document.getElementById('strengthText');

    if (!val) { bar.classList.add('hidden'); return; }
    bar.classList.remove('hidden');

    let strength = 0;
    if (val.length >= 8)                        strength++;
    if (/[A-Z]/.test(val))                      strength++;
    if (/[0-9]/.test(val))                      strength++;
    if (/[^A-Za-z0-9]/.test(val))              strength++;

    const colors = ['bg-red-400','bg-orange-400','bg-yellow-400','bg-green-500'];
    const labels = ['Weak','Fair','Good','Strong'];

    segments.forEach((id, i) => {
        const el = document.getElementById(id);
        el.className = 'h-1.5 flex-1 rounded-full ' +
            (i < strength ? colors[strength-1] : 'bg-gray-200');
    });

    text.textContent = 'Password strength: ' + (labels[strength-1] || 'Too short');
    text.className   = 'text-xs ' + (strength >= 3 ? 'text-green-600' : 'text-orange-500');
});

// Validate password match before submit
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPwd  = document.getElementById('modalNewPassword').value;
    const confPwd = document.getElementById('modalConfirmPassword').value;
    if (newPwd !== confPwd) {
        e.preventDefault();
        alert('Passwords do not match!');
    }
});
</script>

</body>
</html>