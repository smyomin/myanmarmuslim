<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$pageTitle = 'Contact Us';
$success   = '';
$error     = '';

//require_once '../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? 'Contact Form Message');
    $message = trim($_POST['message'] ?? '');

    if (!$name || !$email || !$message) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Email to admin
        $adminBody = buildEmailTemplate(
            'New Contact Form Message',
            "
            <p><strong>From:</strong> " . htmlspecialchars($name) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
            <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
            <hr style='border:none; border-top:1px solid #eee; margin:16px 0;'>
            <p><strong>Message:</strong></p>
            <p style='background:#f9f9f9; padding:16px; border-radius:8px; border-left:4px solid #1a6b3c;'>
                " . nl2br(htmlspecialchars($message)) . "
            </p>
            "
        );

        // Auto-reply to sender
        $replyBody = buildEmailTemplate(
            'We received your message!',
            "
            <p>Assalamu Alaikum, <strong>" . htmlspecialchars($name) . "</strong></p>
            <p>Thank you for contacting us. We have received your message and
               will get back to you within 24-48 hours, In sha Allah.</p>
            <p style='background:#f9f9f9; padding:16px; border-radius:8px;
                      border-left:4px solid #1a6b3c;'>
                <strong>Your message:</strong><br><br>
                " . nl2br(htmlspecialchars($message)) . "
            </p>
            <p>JazakAllahu Khayran,<br>
               <strong>" . SITE_NAME . " Team</strong>
            </p>
            ",
            'Visit Library',
            SITE_URL
        );

        // Send both emails
        $adminResult = sendMail(SMTP_USERNAME, SITE_NAME, 'Contact: ' . $subject, $adminBody);
        $replyResult = sendMail($email, $name, 'Thank you for contacting us - ' . SITE_NAME, $replyBody);

        if ($adminResult['success']) {
            $success = 'Thank you! Your message has been sent. We will reply within 24-48 hours, In sha Allah.';
        } else {
            $error = 'Failed to send message. Please try again or email us directly.';
        }
    }
}

require_once '../includes/seo.php';
$seoOptions = [
    'title'       => 'Contact Us - Myanmar Muslims Library',
    'description' => 'Get in touch with the Myanmar Muslims Islamic Library team.',
    'url'         => SITE_URL . '/pages/contact.php',
];

include '../includes/header.php';
?>

<div class="bg-primary text-white py-12 px-4">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-2">ဆက်သွယ်ရန်</h1>
        <p class="text-green-100">မြန်မာမွတ်စလင်မ်စာကြည့်တိုက်အသင်းနှင့် ဆက်သွယ်ရန်</p>
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Contact Info -->
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-bold text-gray-800 mb-4">Get In Touch</h3>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 bg-green-50 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-envelope text-primary text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Email</p>
                            <p class="text-sm font-medium text-gray-800">
                                <?= SITE_EMAIL ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 bg-green-50 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-globe text-primary text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Website</p>
                            <p class="text-sm font-medium text-gray-800"><?= SITE_EMAIL ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 bg-green-50 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-primary text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Contact Number</p>
                            <p class="text-sm font-medium text-gray-800">+95 9961429405 <br>Whatsapp, Telegram, Imo, Viber (Available)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Follow Us -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-bold text-gray-800 mb-4">Follow Us</h3>
                <div class="flex gap-3">
                    <a href="#" class="w-9 h-9 bg-blue-600 rounded-full flex items-center justify-center text-white hover:bg-blue-700 transition">
                        <i class="fab fa-facebook-f text-sm"></i>
                    </a>
                    <a href="#" class="w-9 h-9 bg-green-500 rounded-full flex items-center justify-center text-white hover:bg-green-600 transition">
                        <i class="fab fa-whatsapp text-sm"></i>
                    </a>
                    <a href="#" class="w-9 h-9 bg-sky-500 rounded-full flex items-center justify-center text-white hover:bg-sky-600 transition">
                        <i class="fab fa-telegram text-sm"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Get in touch -->
        <div class="lg:col-span-2">
            <div class="rounded-xl shadow-sm p-6 md:p-8"
                style="background: var(--color-card); border: 1px solid var(--color-border)">
                <h2 class="text-xl font-bold mb-3" style="color: var(--color-text)">
                    Reach Us
                </h2>
                <p class="text-sm leading-relaxed" style="color: var(--color-text-muted)">
                    For questions, feedback or book requests, please contact us through
                    our social media pages or by email. We reply within 24-48 hours, In sha Allah.
                </p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>