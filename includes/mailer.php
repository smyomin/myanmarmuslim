<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

// ============================================
// SMTP CONFIGURATION (Gmail SMTP with App Password)
// ============================================
/*
define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',     587);
define('SMTP_USERNAME', 'smyomin@gmail.com');      // Your Gmail address
define('SMTP_PASSWORD', 'wljh hzqx vtqq nkqu'); // Your Gmail App Password
define('SMTP_FROM_NAME', SITE_NAME);
define('SMTP_FROM_EMAIL', SMTP_USERNAME);
*/
// ============================================

// ============================================
// SMTP CONFIGURATION (Hostinger SMTP with App Password)
// ============================================

define('SMTP_HOST',     'smtp.hostinger.com');
define('SMTP_PORT',     587);
define('SMTP_USERNAME', 'admin@myanmarmuslim.org');      // Your Hostinger address
define('SMTP_PASSWORD', 'TAz42kzV5Z#9Nqj0h6ZvG&p!32kKs@'); // Your Hostinger App Password
define('SMTP_FROM_NAME', SITE_NAME);
define('SMTP_FROM_EMAIL', SMTP_USERNAME);
// ============================================

function sendMail($toEmail, $toName, $subject, $htmlBody) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);

        // Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;

        $mail->send();
        return ['success' => true];

    } catch (Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}

// Reusable HTML email template
function buildEmailTemplate($title, $body, $buttonText = '', $buttonUrl = '') {
    $siteName = SITE_NAME;
    $siteUrl  = SITE_URL;
    $year     = date('Y');

    $button = '';
    if ($buttonText && $buttonUrl) {
        $button = "
        <div style='text-align:center; margin-top:30px;'>
            <a href='$buttonUrl'
               style='background:#1a6b3c; color:white; padding:12px 32px;
                      border-radius:8px; text-decoration:none; font-weight:bold;
                      font-size:15px;'>
                $buttonText
            </a>
        </div>";
    }

    return "
    <!DOCTYPE html>
    <html>
    <head><meta charset='UTF-8'></head>
    <body style='margin:0; padding:0; background:#f4f4f4; font-family:Arial,sans-serif;'>
        <div style='max-width:600px; margin:30px auto; background:white;
                    border-radius:12px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1);'>

            <!-- Header -->
            <div style='background:#1a6b3c; padding:24px; text-align:center;'>
                <p style='color:#c9a84c; font-size:22px; margin:0 0 6px 0;
                          font-family:Georgia,serif;'>
                    بِسْمِ اللهِ الرَّحْمٰنِ الرَّحِيْمِ
                </p>
                <h2 style='color:white; margin:0; font-size:20px;'>
                    🕌 $siteName
                </h2>
            </div>

            <!-- Body -->
            <div style='padding:32px;'>
                <h3 style='color:#1a6b3c; margin-top:0;'>$title</h3>
                <div style='color:#444; line-height:1.8; font-size:15px;'>
                    $body
                </div>
                $button
            </div>

            <!-- Footer -->
            <div style='background:#f9f9f9; padding:16px; text-align:center;
                        border-top:1px solid #eee;'>
                <p style='color:#999; font-size:12px; margin:0;'>
                    © $year $siteName — Islamic Online Library
                </p>
                <p style='margin:4px 0 0;'>
                    <a href='$siteUrl' style='color:#1a6b3c; font-size:12px;'>
                        Visit our website
                    </a>
                </p>
            </div>
        </div>
    </body>
    </html>";
}