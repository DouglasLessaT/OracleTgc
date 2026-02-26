<?php

namespace App\Service;

use App\Domain\Entity\User;
use Twig\Environment;

/**
 * Envia e-mail com o código de verificação (OTP de 6 dígitos)
 * usando PHPMailer via SMTP e template HTML.
 */
class EmailVerificationService
{
    private const TEMPLATE_VERIFICATION = 'email/verification_token.html.twig';
    private const EXPIRATION_MINUTES = 15;

    public function __construct(
        private PhpMailerService $phpMailer,
        private Environment $twig,
        private string $appName = 'Oracle TGC'
    ) {
    }

    public function sendVerificationCode(User $user, string $code): void
    {
        $html = $this->twig->render(self::TEMPLATE_VERIFICATION, [
            'userName' => $user->getName(),
            'code' => $code,
            'appName' => $this->appName,
            'expirationMinutes' => self::EXPIRATION_MINUTES,
        ]);

        $subject = $this->appName . ' - Código de verificação';

        $this->phpMailer->send($user->getEmail(), $subject, $html);
    }
}
