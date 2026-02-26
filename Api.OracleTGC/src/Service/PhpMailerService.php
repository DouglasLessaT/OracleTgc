<?php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PhpMailerException;

/**
 * Envio de e-mails via PHPMailer + SMTP (Gmail, Outlook, Mailtrap, etc.).
 * Reduz risco de cair em spam quando configurado corretamente.
 */
class PhpMailerService
{
    public function __construct(
        private string $host,
        private int $port,
        private string $user,
        private string $password,
        private string $encryption,
        private string $fromEmail,
        private string $fromName,
        private bool $debug = false
    ) {
    }

    /**
     * Envia um e-mail HTML.
     *
     * @param string $to      E-mail do destinatário
     * @param string $subject  Assunto
     * @param string $htmlBody Corpo HTML (deve ser HTML válido)
     * @throws \RuntimeException Se o envio falhar
     */
    public function send(string $to, string $subject, string $htmlBody): void
    {
        $mail = new PHPMailer(true);

        try {
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->Encoding = 'base64';
            $mail->isSMTP();
            $mail->Host = $this->host;
            $mail->SMTPAuth = $this->user !== '';
            $mail->Username = $this->user;
            $mail->Password = $this->password;
            $mail->SMTPSecure = $this->getSmtpSecure();
            $mail->Port = $this->port;
            $mail->SMTPDebug = $this->debug ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;

            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $htmlBody));

            $mail->send();
        } catch (PhpMailerException $e) {
            throw new \RuntimeException('Falha ao enviar e-mail: ' . $mail->ErrorInfo, 0, $e);
        }
    }

    private function getSmtpSecure(): string
    {
        return match (strtolower($this->encryption)) {
            'ssl' => PHPMailer::ENCRYPTION_SMTPS,
            'tls' => PHPMailer::ENCRYPTION_STARTTLS,
            default => '',
        };
    }
}
