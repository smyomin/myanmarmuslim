<?php

require_once 'auth.php';
require_once '../includes/functions.php';
require_once '../includes/mailer.php';

$success = '';
$error   = '';
$sent    = 0;

// Get subscriber counts
$totalSubs = $pdo->query("SELECT COUNT(*) FROM visitor_emails WHERE is_subscriber = 1")->fetchColumn();
$totalAll  = $pdo->query("SELECT COUNT(*) FROM visitor_emails")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    $subject    = trim($_POST['subject'] ?? '');
    $message    = trim($_POST['message'] ?? '');
    $sendTo     = $_POST['send_to'] ?? 'subscribers';

    if (!$subject || !$message) {
        $error = 'Subject and message are required.';
    } else {
        // Get emails
        if ($sendTo === 'all') {
            $emails = $pdo->query("SELECT email, name FROM visitor_emails")->fetchAll();
        } else {
            $emails = $pdo->query("SELECT email, name FROM visitor_emails WHERE is_subscriber = 1")->fetchAll();
        }

        if (empty($emails)) {
            $error = 'No emails found to send to.';
        } else {
            $siteUrl  = SITE_URL;
            $siteName = SITE_NAME;

                foreach ($emails as $recipient) {
                    $toEmail = $recipient['email'];
                    $toName  = $recipient['name'] ?: 'Brother/Sister';

                    $emailBody = buildEmailTemplate(
                        $subject,
                        "
                        <p>Assalamu Alaikum, <strong>$toName</strong></p>
                        <div>" . nl2br(htmlspecialchars($message)) . "</div>
                        <p style='margin-top:24px; color:#888; font-size:13px;'>
                            You received this because you subscribed to $siteName updates.
                        </p>
                        ",
                        'Visit Library',
                        SITE_URL
                    );

                    $result = sendMail($toEmail, $toName, $subject, $emailBody);
                    if ($result['success']) {
                        $sent++;
                    }
                }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notification - Admin Panel</title>
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
        <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg transition">
            <i class="fas fa-sign-out-alt mr-1"></i>Logout
        </a>
    </div>
</nav>

<div class="flex">
    <aside class="w-64 min-h-screen bg-white shadow-md hidden md:block">
        <nav class="p-4 space-y-1">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-tachometer-alt w-5"></i>Dashboard
            </a>
            <a href="books.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-book w-5"></i>Manage Books
            </a>
            <a href="add-book.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-plus-circle w-5"></i>Add New Book
            </a>
            <a href="news.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-newspaper w-5"></i>Manage News
            </a>
            <a href="add-news.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-plus-circle w-5"></i>Add News
            </a>
            <a href="subscribers.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-envelope w-5"></i>Subscribers
            </a>
            <a href="notify.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-green-50 text-primary font-medium">
                <i class="fas fa-bell w-5"></i>Send Notification
            </a>
            <a href="categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-tags w-5"></i>Categories
            </a>
        </nav>
    </aside>

    <main class="flex-1 p-6">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Send Email Notification</h1>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-purple-500">
                    <p class="text-sm text-gray-500">Subscribers</p>
                    <p class="text-2xl font-bold text-purple-500"><?= $totalSubs ?></p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-primary">
                    <p class="text-sm text-gray-500">Total Emails</p>
                    <p class="text-2xl font-bold text-primary"><?= $totalAll ?></p>
                </div>
            </div>

            <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i><?= clean($success) ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i><?= clean($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
                <?php csrfField(); ?>
                <!-- Send To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Send To</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="send_to" value="subscribers" checked class="accent-primary">
                            <span class="text-sm text-gray-700">
                                Subscribers only <span class="text-purple-500 font-medium">(<?= $totalSubs ?>)</span>
                            </span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="send_to" value="all" class="accent-primary">
                            <span class="text-sm text-gray-700">
                                Everyone <span class="text-primary font-medium">(<?= $totalAll ?>)</span>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Subject -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Email Subject <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="subject"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                           placeholder="e.g. New Islamic book added to the library!" required>
                </div>

                <!-- Message -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Message <span class="text-red-500">*</span>
                    </label>
                    <textarea name="message" rows="8" required
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary resize-none"
                              placeholder="Write your notification message here..."></textarea>
                </div>

                <button type="submit"
                        onclick="return confirm('Send this email to all selected recipients?')"
                        class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-lg transition">
                    <i class="fas fa-paper-plane mr-2"></i>Send Notification
                </button>
            </form>
        </div>
    </main>
</div>

</body>
</html>