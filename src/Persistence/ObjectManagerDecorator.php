<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

use BadMethodCallException;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;

use function get_class;
use function method_exists;
use function sprintf;

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

    /** @psalm-return ClassMetadataFactory<ClassMetadata<object>> */
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
        if (! method_exists($this->wrapped, 'isUninitializedObject')) {
            $wrappedClass = get_class($this->wrapped);

            throw new BadMethodCallException(sprintf(
                <<<'EXCEPTION'
Context: Trying to call %s
Problem: The wrapped ObjectManager, an instance of %s does not implement this method.
Solution: Implement %s::isUninitializedObject() with a signature compatible with this one:
    public function isUninitializedObject(mixed $value): bool
EXCEPTION
                ,
                __METHOD__,
                $wrappedClass,
                $wrappedClass,
            ));
        }

        return $this->wrapped->isUninitializedObject($value);
    }

    public function contains(object $object): bool
    {
        return $this->wrapped->contains($object);
    }
}
