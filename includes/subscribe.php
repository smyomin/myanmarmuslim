<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/mailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ' . SITE_URL . '?msg=invalid');
        exit;
    }

    // Save and mark as subscriber
    if (!emailExists($pdo, $email)) {
        $stmt = $pdo->prepare("INSERT INTO visitor_emails (email, is_subscriber) VALUES (?, 1)");
        $stmt->execute([$email]);
    } else {
        $stmt = $pdo->prepare("UPDATE visitor_emails SET is_subscriber = 1 WHERE email = ?");
        $stmt->execute([$email]);
    }

    // Send welcome email
    $body = buildEmailTemplate(
        'Welcome to ' . SITE_NAME . '!',
        "
        <p>Assalamu Alaikum,</p>
        <p>JazakAllahu Khayran for subscribing to <strong>" . SITE_NAME . "</strong>!</p>
        <p>You will now receive notifications whenever we add new Islamic books,
           duas, research papers and other materials to our library.</p>
        <p>May Allah bless you and your family with beneficial knowledge.</p>
        <p style='margin-top:24px;'>
            <strong>" . SITE_NAME . " Team</strong>
        </p>
        ",
        'Browse Library Now',
        SITE_URL . '/pages/library.php'
    );

    sendMail($email, '', 'Welcome to ' . SITE_NAME . ' Library!', $body);

    header('Location: ' . SITE_URL . '?msg=subscribed');
    exit;
}

header('Location: ' . SITE_URL);
exit;