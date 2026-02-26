<?php

namespace App\Service;

use App\Core\Domain\Exception\ValidationException;
use App\Core\Infrastructure\Security\JWTManager;
use App\Domain\Entity\User;
use App\Repositories\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Auth Service
 *
 * Serviço responsável por autenticação, geração de tokens JWT e verificação de email.
 */
class AuthService
{
    private const VERIFICATION_CODE_LENGTH = 6;

    public function __construct(
        private UserRepository $userRepository,
        private JWTManager $jwtManager,
        private EntityManagerInterface $em,
        private EmailVerificationService $emailVerificationService,
        private ?LoggerInterface $logger = null
    ) {
    }

    /**
     * Registra um novo usuário
     */
    public function register(string $email, string $password, string $name): User
    {
        // Validações
        if ($this->userRepository->emailExists($email)) {
            throw ValidationException::fromErrors([
                'email' => 'Email já está em uso'
            ]);
        }

        if (strlen($password) < 6) {
            throw ValidationException::fromErrors([
                'password' => 'Senha deve ter no mínimo 6 caracteres'
            ]);
        }

        if (empty($name)) {
            throw ValidationException::fromErrors([
                'name' => 'Nome é obrigatório'
            ]);
        }

        // Criar usuário
        $user = new User($email, $password, $name);

        // Gera código de 6 dígitos e envia por email para validação
        $code = $this->generateVerificationCode();
        $user->setEmailVerificationToken($code);
        $this->em->persist($user);
        $this->em->flush();

        $this->emailVerificationService->sendVerificationCode($user, $code);

        return $user;
    }

    /**
     * Gera um código numérico de 6 dígitos para verificação de email.
     */
    private function generateVerificationCode(): string
    {
        $min = (int) str_pad('1', self::VERIFICATION_CODE_LENGTH, '0');
        $max = (int) str_repeat('9', self::VERIFICATION_CODE_LENGTH);

        return (string) random_int($min, $max);
    }

    /**
     * Valida o código de verificação de email e marca o usuário como verificado.
     * Retorna o usuário.
     */
    public function verifyEmailToken(string $token): User
    {
        $user = $this->userRepository->findByEmailVerificationToken($token);

        if (!$user) {
            throw ValidationException::fromErrors([
                'token' => 'Código inválido ou expirado'
            ]);
        }

        $user->setEmailVerificationToken(null);
        $user->setEmailVerifiedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $user;
    }

    /**
     * Gera um token JWT para o usuário (usado após verificação de email para não exigir senha no Complete Profile).
     */
    public function generateTokenForUser(User $user): string
    {
        return $this->jwtManager->generate([
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'type' => $user->getType(),
            'plan' => $user->getPlan(),
            'name' => $user->getName(),
        ]);
    }

    /**
     * Reenvia o código de verificação para o email informado.
     */
    public function resendVerificationCode(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw ValidationException::fromErrors([
                'email' => 'Email não encontrado'
            ]);
        }

        if ($user->isEmailVerified()) {
            throw ValidationException::fromErrors([
                'email' => 'Email já verificado'
            ]);
        }

        $code = $this->generateVerificationCode();
        $user->setEmailVerificationToken($code);
        $this->em->flush();

        $this->emailVerificationService->sendVerificationCode($user, $code);
    }

    /**
     * Envia código de verificação para o e-mail do usuário (para confirmação em alterações de conta).
     * Usado na página de configurações; não exige que o e-mail esteja "não verificado".
     */
    public function sendSettingsVerificationCode(User $user): void
    {
        $code = $this->generateVerificationCode();
        $user->setEmailVerificationToken($code);
        $this->em->flush();

        $this->emailVerificationService->sendVerificationCode($user, $code);
    }

    /**
     * Autentica um usuário e retorna o token JWT
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            $this->logger?->debug('[AUTH_DEBUG] login failed', [
                'reason' => 'user_not_found',
                'email' => substr($email, 0, 3) . '***',
                'hint' => 'Nenhum usuário com este email no banco. Verifique a tabela users.',
            ]);
            throw ValidationException::fromErrors([
                'credentials' => 'Email ou senha inválidos'
            ]);
        }

        if (!$user->isActive()) {
            $this->logger?->debug('[AUTH_DEBUG] login failed', [
                'reason' => 'user_inactive',
                'userId' => $user->getId(),
                'hint' => 'Conta desativada (is_active=false).',
            ]);
            throw ValidationException::fromErrors([
                'account' => 'Conta desativada'
            ]);
        }

        if (!$user->verifyPassword($password)) {
            $this->logger?->debug('[AUTH_DEBUG] login failed', [
                'reason' => 'password_verify_failed',
                'userId' => $user->getId(),
                'email' => substr($user->getEmail(), 0, 3) . '***',
                'passwordLength' => strlen($password),
                'hint' => 'Senha não confere com o hash no banco. Verifique se no registro a senha foi salva corretamente (password_hash).',
            ]);
            throw ValidationException::fromErrors([
                'credentials' => 'Email ou senha inválidos'
            ]);
        }

        // Atualizar último login
        $user->setLastLoginAt(new \DateTimeImmutable());
        $this->em->flush();

        // Gerar token JWT
        $token = $this->jwtManager->generate([
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'type' => $user->getType(),
            'plan' => $user->getPlan(),
            'name' => $user->getName(),
        ]);

        return [
            'token' => $token,
            'user' => $user->toArray(),
        ];
    }

    /**
     * Valida um token JWT e retorna o usuário
     */
    public function validateToken(string $token): ?User
    {
        $payload = $this->jwtManager->decode($token);

        if (!$payload || !isset($payload['sub'])) {
            return null;
        }

        $user = $this->userRepository->findById($payload['sub']);

        if (!$user instanceof User || !$user->isActive()) {
            return null;
        }

        return $user;
    }

    /**
     * Gera um refresh token
     */
    public function refreshToken(string $token): array
    {
        $user = $this->validateToken($token);

        if (!$user) {
            throw ValidationException::fromErrors([
                'token' => 'Token inválido'
            ]);
        }

        $newToken = $this->jwtManager->generate([
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'type' => $user->getType(),
            'plan' => $user->getPlan(),
            'name' => $user->getName(),
        ]);

        return [
            'token' => $newToken,
            'user' => $user->toArray(),
        ];
    }
}

