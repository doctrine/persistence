<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping\Fixtures;

use Doctrine\Persistence\Mapping\ClassMetadata;
use LogicException;
use ReflectionClass;

final class TestClassMetadata implements ClassMetadata
{
    /**
     * @var string
     * @psalm-var class-string
     */
    private $className;

    /**
     * @psalm-param class-string $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * @psalm-return class-string
     */
    public function getName(): string
    {
        return $this->className;
    }

    /**
     * @return string[]
     */
    public function getIdentifier(): array
    {
        return ['id'];
    }

    public function getReflectionClass(): ReflectionClass
    {
        return new ReflectionClass($this->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function isIdentifier($fieldName): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function hasField($fieldName): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAssociation($fieldName)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isSingleValuedAssociation($fieldName)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isCollectionValuedAssociation($fieldName)
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
    public function getTypeOfField($fieldName)
    {
        throw new LogicException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationTargetClass($assocName)
    {
        throw new LogicException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isAssociationInverseSide($assocName): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationMappedByTargetField($assocName)
    {
        throw new LogicException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierValues($object): array
    {
        return [];
    }
}
