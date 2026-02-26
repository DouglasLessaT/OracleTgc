<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User Entity
 *
 * Duas características distintas:
 * - type: papel no sistema (guest, customer, admin)
 * - plan: plano de assinatura (free, premium) — define limites e funcionalidades
 *
 * Implementa UserInterface para o Security do Symfony (JWT + entity provider).
 */
#[ORM\Entity]
#[ORM\Table(name: "users")]
class User extends BaseEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    /** Tipo/papel do usuário no sistema */
    public const TYPE_GUEST = 'guest';
    public const TYPE_CUSTOMER = 'customer';
    public const TYPE_ADMIN = 'admin';

    /** Plano de assinatura (funcionalidades e limites) */
    public const PLAN_FREE = 'free';
    public const PLAN_PREMIUM = 'premium';

    #[ORM\Id]
    #[ORM\Column(type: "uuid_string", unique: true)]
    protected mixed $id;

    #[ORM\Column(length: 255, unique: true)]
    private string $email;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\Column(length: 100)]
    private string $name;

    /** Papel no sistema: guest, customer, admin */
    #[ORM\Column(length: 20)]
    private string $type = self::TYPE_GUEST;

    /** Plano de assinatura: free, premium (define coleções, decks, etc.) */
    #[ORM\Column(length: 20)]
    private string $plan = self::PLAN_FREE;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $scannedCardsCount = 0; // Contador de cards escaneados no período

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $lastResetAt = null; // Última vez que o contador foi resetado

    /** Código de 6 dígitos para verificação de email (OTP) */
    #[ORM\Column(length: 6, nullable: true)]
    private ?string $emailVerificationToken = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $emailVerifiedAt = null;

    /** Perfil público (dados de exibição e contadores). Opcional: criado sob demanda. */
    #[ORM\OneToOne(mappedBy: "user", targetEntity: UserProfile::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private ?UserProfile $profile = null;

    public function __construct(string $email, string $password, string $name)
    {
        parent::__construct();
        $this->email = $email;
        $this->setPassword($password);
        $this->name = $name;
        $this->lastResetAt = new \DateTimeImmutable();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
        $this->markAsUpdated();
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
        $this->markAsUpdated();
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
        $this->markAsUpdated();
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /** @inheritdoc */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /** @inheritdoc */
    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];
        if ($this->type === self::TYPE_ADMIN) {
            $roles[] = 'ROLE_ADMIN';
        }
        if ($this->plan === self::PLAN_PREMIUM) {
            $roles[] = 'ROLE_PREMIUM';
        }
        return $roles;
    }

    /** @inheritdoc */
    public function eraseCredentials(): void
    {
        // Nada sensível em memória além da senha (já hasheada).
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->markAsUpdated();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        if (!in_array($type, [self::TYPE_GUEST, self::TYPE_CUSTOMER, self::TYPE_ADMIN], true)) {
            throw new \InvalidArgumentException("Invalid user type: {$type}");
        }
        $this->type = $type;
        $this->markAsUpdated();
    }

    public function getPlan(): string
    {
        return $this->plan;
    }

    public function setPlan(string $plan): void
    {
        if (!in_array($plan, [self::PLAN_FREE, self::PLAN_PREMIUM], true)) {
            throw new \InvalidArgumentException("Invalid user plan: {$plan}");
        }
        $this->plan = $plan;
        $this->markAsUpdated();
    }

    /** Usuário tem plano gratuito (limites de scan e uma coleção “all” por jogo) */
    public function isFree(): bool
    {
        return $this->plan === self::PLAN_FREE;
    }

    /** Usuário tem plano premium ou é admin (coleções ilimitadas, decks, etc.) */
    public function isPremium(): bool
    {
        return $this->plan === self::PLAN_PREMIUM || $this->type === self::TYPE_ADMIN;
    }

    /** Usuário é administrador do sistema */
    public function isAdmin(): bool
    {
        return $this->type === self::TYPE_ADMIN;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
        $this->markAsUpdated();
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): void
    {
        $this->lastLoginAt = $lastLoginAt;
        $this->markAsUpdated();
    }

    public function getScannedCardsCount(): int
    {
        return $this->scannedCardsCount;
    }

    public function incrementScannedCards(): void
    {
        $this->scannedCardsCount++;
        $this->markAsUpdated();
    }

    public function resetScannedCardsCount(): void
    {
        $this->scannedCardsCount = 0;
        $this->lastResetAt = new \DateTimeImmutable();
        $this->markAsUpdated();
    }

    public function getLastResetAt(): ?\DateTimeImmutable
    {
        return $this->lastResetAt;
    }

    public function getEmailVerificationToken(): ?string
    {
        return $this->emailVerificationToken;
    }

    public function setEmailVerificationToken(?string $emailVerificationToken): void
    {
        $this->emailVerificationToken = $emailVerificationToken;
        $this->markAsUpdated();
    }

    public function getEmailVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function setEmailVerifiedAt(?\DateTimeImmutable $emailVerifiedAt): void
    {
        $this->emailVerifiedAt = $emailVerifiedAt;
        $this->markAsUpdated();
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    public function getProfile(): ?UserProfile
    {
        return $this->profile;
    }

    public function setProfile(?UserProfile $profile): void
    {
        $this->profile = $profile;
    }

    /**
     * Verifica se o usuário pode escanear mais cards (limite de 7 para usuários gratuitos)
     */
    public function canScanCard(): bool
    {
        if ($this->isPremium() || $this->isAdmin()) {
            return true; // Sem limite
        }

        // Usuário gratuito: máximo 7 cards por anúncio
        // Verifica se precisa resetar o contador (a cada 24 horas)
        if ($this->lastResetAt) {
            $now = new \DateTimeImmutable();
            $diff = $now->diff($this->lastResetAt);
            if ($diff->days >= 1) {
                $this->resetScannedCardsCount();
            }
        }

        return $this->scannedCardsCount < 7;
    }

    /**
     * Verifica se o usuário pode criar coleções (apenas premium)
     */
    public function canCreateCollections(): bool
    {
        return $this->isPremium() || $this->isAdmin();
    }

    /**
     * Verifica se o usuário pode criar decks (apenas premium)
     */
    public function canCreateDecks(): bool
    {
        return $this->isPremium() || $this->isAdmin();
    }

    /**
     * Verifica se o usuário pode gerenciar outros usuários (apenas admin)
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Verifica se o usuário pode gerar relatórios (apenas admin)
     */
    public function canGenerateReports(): bool
    {
        return $this->isAdmin();
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'email' => $this->email,
            'phone' => $this->phone,
            'name' => $this->name,
            'type' => $this->type,
            'plan' => $this->plan,
            'isActive' => $this->isActive,
            'lastLoginAt' => $this->lastLoginAt?->format('Y-m-d H:i:s'),
            'scannedCardsCount' => $this->scannedCardsCount,
            'emailVerified' => $this->isEmailVerified(),
            'canScanCard' => $this->canScanCard(),
            'canCreateCollections' => $this->canCreateCollections(),
            'canCreateDecks' => $this->canCreateDecks(),
        ]);
    }
}

