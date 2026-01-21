<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Override;

/**
 * Base class to simplify ObjectManager decorators
 *
 * @template-covariant TObjectManager of ObjectManager
 */
abstract class ObjectManagerDecorator implements ObjectManager
{
    /** @var TObjectManager */
    protected ObjectManager $wrapped;

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function find(string $className, $id): object|null
    {
        return $this->wrapped->find($className, $id);
    }

    #[Override]
    public function persist(object $object): void
    {
        $this->wrapped->persist($object);
    }

    #[Override]
    public function remove(object $object): void
    {
        $this->wrapped->remove($object);
    }

    #[Override]
    public function clear(): void
    {
        $this->wrapped->clear();
    }

    #[Override]
    public function detach(object $object): void
    {
        $this->wrapped->detach($object);
    }

    #[Override]
    public function refresh(object $object): void
    {
        $this->wrapped->refresh($object);
    }

    #[Override]
    public function flush(): void
    {
        $this->wrapped->flush();
    }

    #[Override]
    public function getRepository(string $className): ObjectRepository
    {
        return $this->wrapped->getRepository($className);
    }

    #[Override]
    public function getClassMetadata(string $className): ClassMetadata
    {
        return $this->wrapped->getClassMetadata($className);
    }

    /** @phpstan-return ClassMetadataFactory<ClassMetadata<object>> */
    #[Override]
    public function getMetadataFactory(): ClassMetadataFactory
    {
        return $this->wrapped->getMetadataFactory();
    }

    #[Override]
    public function initializeObject(object $obj): void
    {
        $this->wrapped->initializeObject($obj);
    }

    #[Override]
    public function isUninitializedObject(mixed $value): bool
    {
        return $this->wrapped->isUninitializedObject($value);
    }

    #[Override]
    public function contains(object $object): bool
    {
        return $this->wrapped->contains($object);
    }
}
