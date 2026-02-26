<?php

namespace App\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Uid\Uuid;

/**
 * Armazena UUID como string legível (CHAR 36) no banco, ex.: 550e8400-e29b-41d4-a716-446655440000.
 * O tipo padrão "uuid" do Symfony usa binary(16), que aparece como "criptografado" no banco.
 */
class UuidStringType extends Type
{
    public const NAME = 'uuid_string';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['length' => 36, 'fixed' => true]);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Uuid
    {
        if ($value instanceof Uuid || null === $value || $value === '') {
            return $value instanceof Uuid ? $value : null;
        }
        if (!\is_string($value)) {
            return null;
        }
        try {
            return Uuid::fromString($value);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof Uuid) {
            return $value->toRfc4122();
        }
        if (null === $value || $value === '') {
            return null;
        }
        if (\is_string($value)) {
            try {
                return Uuid::fromString($value)->toRfc4122();
            } catch (\InvalidArgumentException $e) {
                return $value;
            }
        }
        return null;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
