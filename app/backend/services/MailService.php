<?php

ini_set('display_errors', 1);
ini_set('log_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../vendor/autoload.php';

class MailService
{
    private static function configureMailer(): PHPMailer
    {
        $keys = require __DIR__ . '/../config/keys.php';

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $keys['phpmailer']['host'];
        $mail->SMTPAuth = $keys['phpmailer']['auth'];
        $mail->Username = $keys['phpmailer']['username'];
        $mail->Password = $keys['phpmailer']['password'];
        $mail->SMTPSecure = strtolower($keys['phpmailer']['security']) === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $keys['phpmailer']['port'];

        $mail->setFrom($keys['phpmailer']['from'], $keys['platform']['name']);
        $mail->isHTML(true);

        return $mail;
    }

    public static function sendOtpEmail(string $toEmail, string $otp, string $type = 'register'): bool
    {
        $platform = require __DIR__ . '/../config/keys.php';
        $platformName = $platform['platform']['name'];

        $subject = $type === 'forgot-password'
            ? "Reset Your Password"
            : "Welcome to $platformName";
        $message = $type === 'forgot-password'
            //Forgot password
            ? "We received a request to reset your password. <br><br>
            Use the one-time password (OTP) below to proceed: <br><br>
            <strong>$otp</strong> <br><br>
            This code is valid for 30 minutes. If you didn't request a password reset, you can safely ignore this email.
            "
            //Register
            : "Welcome aboard! To complete your registration, please use the one-time password (OTP) below.<br><br>
            Your OTP is: <strong>$otp</strong>.<br>
            This code will expire in 30 minutes.";

        return self::sendEmail($toEmail, $subject, $message);
    }

    public static function sendContactFormToAdmin(string $name, string $email, string $message): bool
    {
        $platform = require __DIR__ . '/../config/keys.php';
        $adminEmail = $platform['platform']['supportmail'] ?? $platform['phpmailer']['from'];
        $subject = "New Contact Message from $name";

        $body = "<p><strong>Name:</strong> $name</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Message:</strong><br>$message</p>";

        return self::sendEmail($adminEmail, $subject, $body);
    }

    private static function sendEmail(string $toEmail, string $subject, string $htmlMessage): bool
    {
        try {
            $keys = require __DIR__ . '/../config/keys.php';
            $platformName = $keys['platform']['name'];
            $mail = self::configureMailer();
            $mail->addAddress($toEmail);
            $mail->Subject = $subject;

            $template = file_get_contents(__DIR__ . '/../templates/mail.html');
            $replacements = [
                '{{header}}' => $subject,
                '{{platform_name}}' => $platformName,
                '{{subject}}' => $subject,
                '{{msg}}' => $htmlMessage,
                '{{address}}' => $keys['platform']['address']
            ];

            $previewText = '<div style="display: none; max-height: 0px; overflow: hidden;">' .
                strip_tags($htmlMessage) .
                str_repeat('&nbsp;&zwnj;', 100) . '</div>';

            $template = $previewText . $template;
            $template = str_replace(array_keys($replacements), array_values($replacements), $template);

            $mail->Body = $template;
            $mail->AltBody = strip_tags($htmlMessage);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: {$e->getMessage()}");
            return false;
        }
    }
}
?>