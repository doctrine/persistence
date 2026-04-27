<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping\Fixtures;

use Doctrine\Persistence\Mapping\ClassMetadata;
use LogicException;
use Override;
use ReflectionClass;

/**
 * @template-covariant T of object
 * @template-implements ClassMetadata<T>
 */
final class TestClassMetadata implements ClassMetadata
{
    /** @phpstan-param class-string<T> $className */
    public function __construct(private readonly string $className)
    {
    }

    #[Override]
    public function getName(): string
    {
        return $this->className;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getIdentifier(): array
    {
        return ['id'];
    }

    #[Override]
    public function getReflectionClass(): ReflectionClass
    {
        return new ReflectionClass($this->getName());
    }

    #[Override]
    public function isIdentifier(string $fieldName): bool
    {
        return false;
    }

    #[Override]
    public function hasField(string $fieldName): bool
    {
        return false;
    }

    #[Override]
    public function hasAssociation(string $fieldName): bool
    {
        return false;
    }

    #[Override]
    public function isSingleValuedAssociation(string $fieldName): bool
    {
        return false;
    }

    #[Override]
    public function isCollectionValuedAssociation(string $fieldName): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getFieldNames(): array
    {
        return [];
    }

    #[Override]
    public function getFieldValue(object $object, string $field): mixed
    {
        throw new LogicException('Not implemented');
    }

    #[Override]
    public function setFieldValue(object $object, string $field, mixed $value): void
    {
        throw new LogicException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getIdentifierFieldNames(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getAssociationNames(): array
    {
        return [];
    }

    #[Override]
    public function getTypeOfField(string $fieldName): never
    {
        throw new LogicException('Not implemented');
    }

    #[Override]
    public function getAssociationTargetClass(string $assocName): never
    {
        throw new LogicException('Not implemented');
    }

    #[Override]
    public function isAssociationInverseSide(string $assocName): bool
    {
        return false;
    }

    #[Override]
    public function getAssociationMappedByTargetField(string $assocName): never
    {
        throw new LogicException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getIdentifierValues(object $object): array
    {
        return [];
    }
}
