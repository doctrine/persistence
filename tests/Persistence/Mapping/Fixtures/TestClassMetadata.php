<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping\Fixtures;

use Doctrine\Persistence\Mapping\ClassMetadata;
use LogicException;
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

    public function getName(): string
    {
        return $this->className;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier(): array
    {
        return ['id'];
    }

    public function getReflectionClass(): ReflectionClass
    {
        return new ReflectionClass($this->getName());
    }

    public function isIdentifier(string $fieldName): bool
    {
        return false;
    }

    public function hasField(string $fieldName): bool
    {
        return false;
    }

    public function hasAssociation(string $fieldName): bool
    {
        return false;
    }

    public function isSingleValuedAssociation(string $fieldName): bool
    {
        return false;
    }

    public function isCollectionValuedAssociation(string $fieldName): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldNames(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierFieldNames(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationNames(): array
    {
        return [];
    }

    public function getTypeOfField(string $fieldName): never
    {
        throw new LogicException('Not implemented');
    }

    public function getAssociationTargetClass(string $assocName): never
    {
        throw new LogicException('Not implemented');
    }

    public function isAssociationInverseSide(string $assocName): bool
    {
        return false;
    }

    public function getAssociationMappedByTargetField(string $assocName): never
    {
        throw new LogicException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierValues(object $object): array
    {
        return [];
    }
}
