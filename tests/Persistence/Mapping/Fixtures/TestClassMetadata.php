<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping\Fixtures;

use Doctrine\Persistence\Mapping\ClassMetadata;
use LogicException;
use ReflectionClass;

/**
 * @template T of object
 * @template-implements ClassMetadata<T>
 */
final class TestClassMetadata implements ClassMetadata
{
    /**
     * @var string
     * @psalm-var class-string<T>
     */
    private $className;

    /** @psalm-param class-string<T> $className */
    public function __construct(string $className)
    {
        $this->className = $className;
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

    /**
     * {@inheritDoc}
     */
    public function hasAssociation(string $fieldName)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isSingleValuedAssociation(string $fieldName)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isCollectionValuedAssociation(string $fieldName)
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

    /**
     * {@inheritDoc}
     */
    public function getTypeOfField(string $fieldName)
    {
        throw new LogicException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationTargetClass(string $assocName)
    {
        throw new LogicException('Not implemented');
    }

    public function isAssociationInverseSide(string $assocName): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationMappedByTargetField(string $assocName)
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
