<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;

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
    public function find(string $className, $id): object|null
    {
        return $this->wrapped->find($className, $id);
    }

    public function persist(object $object): void
    {
        $this->wrapped->persist($object);
    }

    public function remove(object $object): void
    {
        $this->wrapped->remove($object);
    }

    public function clear(): void
    {
        $this->wrapped->clear();
    }

    public function detach(object $object): void
    {
        $this->wrapped->detach($object);
    }

    public function refresh(object $object): void
    {
        $this->wrapped->refresh($object);
    }

    public function flush(): void
    {
        $this->wrapped->flush();
    }

    public function getRepository(string $className): ObjectRepository
    {
        return $this->wrapped->getRepository($className);
    }

    public function getClassMetadata(string $className): ClassMetadata
    {
        return $this->wrapped->getClassMetadata($className);
    }

    /** @phpstan-return ClassMetadataFactory<ClassMetadata<object>> */
    public function getMetadataFactory(): ClassMetadataFactory
    {
        return $this->wrapped->getMetadataFactory();
    }

    public function initializeObject(object $obj): void
    {
        $this->wrapped->initializeObject($obj);
    }

    public function isUninitializedObject(mixed $value): bool
    {
        return $this->wrapped->isUninitializedObject($value);
    }

    public function contains(object $object): bool
    {
        return $this->wrapped->contains($object);
    }
}
