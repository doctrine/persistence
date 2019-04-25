<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping;

/**
 * Contract for a Doctrine persistence layer ClassMetadata class to implement.
 */
interface ClassMetadataFactory
{
    /**
     * Forces the factory to load the metadata of all classes known to the underlying
     * mapping driver.
     *
     * @return array<int, ClassMetadata> The ClassMetadata instances of all mapped classes.
     */
    public function getAllMetadata() : array;

    /**
     * Gets the class metadata descriptor for a class.
     *
     * @param string $className The name of the class.
     */
    public function getMetadataFor(string $className) : ClassMetadata;

    /**
     * Checks whether the factory has the metadata for a class loaded already.
     *
     * @return bool TRUE if the metadata of the class in question is already loaded, FALSE otherwise.
     */
    public function hasMetadataFor(string $className) : bool;

    /**
     * Sets the metadata descriptor for a specific class.
     */
    public function setMetadataFor(string $className, ClassMetadata $class) : void;

    /**
     * Returns whether the class with the specified name should have its metadata loaded.
     * This is only the case if it is either mapped directly or as a MappedSuperclass.
     */
    public function isTransient(string $className) : bool;
}
