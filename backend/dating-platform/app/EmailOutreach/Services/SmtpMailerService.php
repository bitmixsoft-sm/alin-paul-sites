<?php

namespace App\EmailOutreach\Services;

use App\EmailOutreach\Models\EmailOutreachAccount;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Sends via the stored account's own SMTP credentials - deliberately NOT through Laravel's
 * Mail facade (whose config structure differs between Laravel 5.7's config/mail.php 'driver'
 * key and 9+'s 'mailers'/'transport' keys), so this whole feature stays drop-in portable
 * across sites on different Laravel versions without touching each site's existing mail setup
 * (used for password resets etc. - not something this feature should interfere with).
 */
class SmtpMailerService
{
    /**
     * @return array{success: bool, error: ?string}
     */
    public function send(EmailOutreachAccount $account, string $toEmail, string $subject, string $htmlBody, ?string $imagePath = null): array
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $account->smtp_host;
            $mail->Port = $account->smtp_port;
            $mail->SMTPAuth = true;
            $mail->Username = $account->smtp_username;
            $mail->Password = $account->smtp_password;
            $mail->SMTPSecure = $account->smtp_encryption === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($account->from_email, $account->from_name ?: $account->from_email);
            $mail->addAddress($toEmail);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $htmlBody;

            if ($imagePath && is_file($imagePath)) {
                // Embedded (not attached) so it renders inline wherever the message body
                // references it via cid:outreach-image - see EmailSequenceEngine.
                $mail->addEmbeddedImage($imagePath, 'outreach-image');
            }

            $mail->send();

            return ['success' => true, 'error' => null];
        } catch (PHPMailerException $e) {
            return ['success' => false, 'error' => $mail->ErrorInfo ?: $e->getMessage()];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
