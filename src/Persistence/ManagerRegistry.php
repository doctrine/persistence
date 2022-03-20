<?php

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
    public function getDefaultManagerName();

    /**
     * Gets a named object manager.
     *
     * @param string|null $name The object manager name (null for the default one).
     *
     * @return ObjectManager
     */
    public function getManager($name = null);

    /**
     * Gets an array of all registered object managers.
     *
     * @return ObjectManager[] An array of ObjectManager instances
     */
    public function getManagers();

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
     *
     * @return ObjectManager
     */
    public function resetManager($name = null);

    /**
     * Resolves a registered namespace alias to the full namespace.
     *
     * This method looks for the alias in all registered object managers.
     *
     * @deprecated This method is deprecated along with short namespace aliases.
     *
     * @param string $alias The alias.
     *
     * @return string The full namespace.
     */
    public function getAliasNamespace($alias);

    /**
     * Gets all object manager names and associated service IDs. A service ID
     * is a string that allows to obtain an object manager, typically from a
     * PSR-11 container.
     *
     * @return array<string,string> An array with object manager names as keys,
     *                              and service IDs as values.
     */
    public function getManagerNames();

    /**
     * Gets the ObjectRepository for a persistent object.
     *
     * @param string      $persistentObject      The name of the persistent object.
     * @param string|null $persistentManagerName The object manager name (null for the default one).
     * @psalm-param class-string<T> $persistentObject
     *
     * @return ObjectRepository
     * @psalm-return ObjectRepository<T>
     *
     * @template T
     */
    public function getRepository($persistentObject, $persistentManagerName = null);

    /**
     * Gets the object manager associated with a given class.
     *
     * @param string $class A persistent object class name.
     *
     * @return ObjectManager|null
     */
    public function getManagerForClass($class);
}
