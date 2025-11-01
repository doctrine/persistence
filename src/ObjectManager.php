<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;

/**
 * Contract for a Doctrine persistence layer ObjectManager class to implement.
 *
 * @method bool isUninitializedObject(mixed $value) Implementing this method will be mandatory in version 4.
 */
interface ObjectManager
{
    /**
     * Finds an object by its identifier.
     *
     * This is just a convenient shortcut for getRepository($className)->find($id).
     *
     * @param string $className The class name of the object to find.
     * @param mixed  $id        The identity of the object to find.
     * @phpstan-param class-string<T> $className
     *
     * @return object|null The found object.
     * @phpstan-return T|null
     *
     * @template T of object
     */
    public function find(string $className, $id);

    /**
     * Tells the ObjectManager to make an instance managed and persistent.
     *
     * The object will be entered into the database as a result of the flush operation.
     *
     * NOTE: The persist operation always considers objects that are not yet known to
     * this ObjectManager as NEW. Do not pass detached objects to the persist operation.
     *
     * @param object $object The instance to make managed and persistent.
     *
     * @return void
     */
    public function persist(object $object);

    /**
     * Removes an object instance.
     *
     * A removed object will be removed from the database as a result of the flush operation.
     *
     * @param object $object The object instance to remove.
     *
     * @return void
     */
    public function remove(object $object);

    /**
     * Clears the ObjectManager. All objects that are currently managed
     * by this ObjectManager become detached.
     *
     * @return void
     */
    public function clear();

    /**
     * Detaches an object from the ObjectManager, causing a managed object to
     * become detached. Unflushed changes made to the object if any
     * (including removal of the object), will not be synchronized to the database.
     * Objects which previously referenced the detached object will continue to
     * reference it.
     *
     * @param object $object The object to detach.
     *
     * @return void
     */
    public function detach(object $object);

    /**
     * Refreshes the persistent state of an object from the database,
     * overriding any local changes that have not yet been persisted.
     *
     * @param object $object The object to refresh.
     *
     * @return void
     */
    public function refresh(object $object);

    /**
     * Flushes all changes to objects that have been queued up to now to the database.
     * This effectively synchronizes the in-memory state of managed objects with the
     * database.
     *
     * @return void
     */
    public function flush();

    /**
     * Gets the repository for a class.
     *
     * @phpstan-param class-string<T> $className
     *
     * @phpstan-return ObjectRepository<T>
     *
     * @template T of object
     */
    public function getRepository(string $className);

    /**
     * Returns the ClassMetadata descriptor for a class.
     *
     * The class name must be the fully-qualified class name without a leading backslash
     * (as it is returned by get_class($obj)).
     *
     * @phpstan-param class-string<T> $className
     *
     * @phpstan-return ClassMetadata<T>
     *
     * @template T of object
     */
    public function getClassMetadata(string $className);

    /**
     * Gets the metadata factory used to gather the metadata of classes.
     *
     * @phpstan-return ClassMetadataFactory<ClassMetadata<object>>
     */
    public function getMetadataFactory();

    /**
     * Helper method to initialize a lazy loading proxy or persistent collection.
     *
     * This method is a no-op for other objects.
     *
     * @return void
     */
    public function initializeObject(object $obj);

    /**
     * Checks if the object is part of the current UnitOfWork and therefore managed.
     *
     * @return bool
     */
    public function contains(object $object);
}
