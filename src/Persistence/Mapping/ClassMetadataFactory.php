<?php

namespace Doctrine\Persistence\Mapping;

/**
 * Contract for a Doctrine persistence layer ClassMetadata class to implement.
 *
 * @template T of ClassMetadata
 */
interface ClassMetadataFactory
{
    /**
     * Forces the factory to load the metadata of all classes known to the underlying
     * mapping driver.
     *
     * @return ClassMetadata[] The ClassMetadata instances of all mapped classes.
     * @psalm-return list<T>
     */
    public function getAllMetadata();

    /**
     * Gets the class metadata descriptor for a class.
     *
     * @param string $className The name of the class.
     *
     * @return ClassMetadata
     * @psalm-return T
     */
    public function getMetadataFor($className);

    /**
     * Checks whether the factory has the metadata for a class loaded already.
     *
     * @param string $className
     *
     * @return bool TRUE if the metadata of the class in question is already loaded, FALSE otherwise.
     */
    public function hasMetadataFor($className);

    /**
     * Sets the metadata descriptor for a specific class.
     *
     * @param string        $className
     * @param ClassMetadata $class
     * @psalm-param T $class
     *
     * @return void
     */
    public function setMetadataFor($className, $class);

    /**
     * Returns whether the class with the specified name should have its metadata loaded.
     * This is only the case if it is either mapped directly or as a MappedSuperclass.
     *
     * @param string $className
     * @psalm-param class-string $className
     *
     * @return bool
     */
    public function isTransient($className);
}
