<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

/**
 * Contract covering object managers for a Doctrine persistence layer ManagerRegistry class to implement.
 */
interface ManagerRegistry extends ConnectionRegistry
{
    /**
     * Gets the default object manager name.
     *
     * @return string The default object manager name.
     */
    public function getDefaultManagerName(): string;

    /**
     * Gets a named object manager.
     *
     * @param string|null $name The object manager name (null for the default one).
     */
    public function getManager(string|null $name = null): ObjectManager;

    /**
     * Gets an array of all registered object managers.
     *
     * @return array<string, ObjectManager> An array of ObjectManager instances
     */
    public function getManagers(): array;

    /**
     * Resets a named object manager.
     *
     * This method is useful when an object manager has been closed
     * because of a rollbacked transaction AND when you think that
     * it makes sense to get a new one to replace the closed one.
     *
     * Be warned that you will get a brand new object manager as
     * the existing one is not useable anymore. This means that any
     * other object with a dependency on this object manager will
     * hold an obsolete reference. You can inject the registry instead
     * to avoid this problem.
     *
     * @param string|null $name The object manager name (null for the default one).
     */
    public function resetManager(string|null $name = null): ObjectManager;

    /**
     * Gets all object manager names and associated service IDs. A service ID
     * is a string that allows to obtain an object manager, typically from a
     * PSR-11 container.
     *
     * @return array<string,string> An array with object manager names as keys,
     *                              and service IDs as values.
     */
    public function getManagerNames(): array;

    /**
     * Gets the ObjectRepository for a persistent object.
     *
     * @param string      $persistentObject      The name of the persistent object.
     * @param string|null $persistentManagerName The object manager name (null for the default one).
     * @phpstan-param class-string<T> $persistentObject
     *
     * @phpstan-return ObjectRepository<T>
     *
     * @template T of object
     */
    public function getRepository(
        string $persistentObject,
        string|null $persistentManagerName = null,
    ): ObjectRepository;

    /**
     * Gets the object manager associated with a given class.
     *
     * @param class-string $class A persistent object class name.
     */
    public function getManagerForClass(string $class): ObjectManager|null;
}
