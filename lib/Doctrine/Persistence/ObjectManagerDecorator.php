<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;

/**
 * Base class to simplify ObjectManager decorators
 */
abstract class ObjectManagerDecorator implements ObjectManager
{
    /** @var ObjectManager */
    protected $wrapped;

    /**
     * {@inheritdoc}
     */
    public function find(string $className, $id) : ?object
    {
        return $this->wrapped->find($className, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function persist(object $object) : void
    {
        $this->wrapped->persist($object);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(object $object) : void
    {
        $this->wrapped->remove($object);
    }

    /**
     * {@inheritdoc}
     */
    public function merge(object $object) : object
    {
        return $this->wrapped->merge($object);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(?string $objectName = null) : void
    {
        $this->wrapped->clear($objectName);
    }

    /**
     * {@inheritdoc}
     */
    public function detach(object $object) : void
    {
        $this->wrapped->detach($object);
    }

    /**
     * {@inheritdoc}
     */
    public function refresh(object $object) : void
    {
        $this->wrapped->refresh($object);
    }

    /**
     * {@inheritdoc}
     */
    public function flush() : void
    {
        $this->wrapped->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(string $className) : ObjectRepository
    {
        return $this->wrapped->getRepository($className);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassMetadata(string $className) : ClassMetadata
    {
        return $this->wrapped->getClassMetadata($className);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFactory() : ClassMetadataFactory
    {
        return $this->wrapped->getMetadataFactory();
    }

    /**
     * {@inheritdoc}
     */
    public function initializeObject(object $obj) : void
    {
        $this->wrapped->initializeObject($obj);
    }

    /**
     * {@inheritdoc}
     */
    public function contains(object $object) : bool
    {
        return $this->wrapped->contains($object);
    }
}
