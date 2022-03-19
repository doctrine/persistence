<?php

declare(strict_types=1);

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
     * @psalm-return T[]
     */
    public function getAllMetadata();

    /**
     * Gets the class metadata descriptor for a class.
     *
     * @param class-string $className The name of the class.
     *
     * @return ClassMetadata
     * @psalm-return T
     */
    public function getMetadataFor(string $className);

    /**
     * Checks whether the factory has the metadata for a class loaded already.
     *
     * @param class-string $className
     *
     * @return bool TRUE if the metadata of the class in question is already loaded, FALSE otherwise.
     */
    public function hasMetadataFor(string $className);

    /**
     * Sets the metadata descriptor for a specific class.
     *
     * @param class-string $className
     * @psalm-param T $class
     *
     * @return void
     */
    public function setMetadataFor(string $className, ClassMetadata $class);

    /**
     * Returns whether the class with the specified name should have its metadata loaded.
     * This is only the case if it is either mapped directly or as a MappedSuperclass.
     *
     * @psalm-param class-string $className
     *
     * @return bool
     */
    public function isTransient(string $className);
}
