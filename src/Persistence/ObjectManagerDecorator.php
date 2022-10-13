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
    protected $wrapped;

    /**
     * {@inheritdoc}
     */
    public function find(string $className, $id)
    {
        return $this->wrapped->find($className, $id);
    }

    public function persist(object $object)
    {
        $this->wrapped->persist($object);
    }

    public function remove(object $object)
    {
        $this->wrapped->remove($object);
    }

    public function clear(): void
    {
        $this->wrapped->clear();
    }

    public function detach(object $object)
    {
        $this->wrapped->detach($object);
    }

    public function refresh(object $object)
    {
        $this->wrapped->refresh($object);
    }

    public function flush()
    {
        $this->wrapped->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(string $className)
    {
        return $this->wrapped->getRepository($className);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassMetadata(string $className)
    {
        return $this->wrapped->getClassMetadata($className);
    }

    /** @psalm-return ClassMetadataFactory<ClassMetadata<object>> */
    public function getMetadataFactory()
    {
        return $this->wrapped->getMetadataFactory();
    }

    public function initializeObject(object $obj)
    {
        $this->wrapped->initializeObject($obj);
    }

    /**
     * {@inheritdoc}
     */
    public function contains(object $object)
    {
        return $this->wrapped->contains($object);
    }
}
