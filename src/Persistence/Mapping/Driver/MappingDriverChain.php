<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\MappingException;

use function array_keys;
use function dirname;
use function spl_object_hash;
use function trim;

/**
 * The DriverChain allows you to add multiple other mapping drivers for
 * certain namespaces.
 */
class MappingDriverChain implements MappingDriver
{
    /**
     * The default driver.
     *
     * @var MappingDriver|null
     */
    private $defaultDriver;

    /** @var array<string, MappingDriver> */
    private $drivers = [];

    /**
     * Gets the default driver.
     *
     * @return MappingDriver|null
     */
    public function getDefaultDriver()
    {
        return $this->defaultDriver;
    }

    /**
     * Set the default driver.
     *
     * @return void
     */
    public function setDefaultDriver(MappingDriver $driver)
    {
        $this->defaultDriver = $driver;
    }

    /**
     * Adds a nested driver.
     *
     * @return void
     */
    public function addDriver(MappingDriver $nestedDriver, string $namespace)
    {
        $this->drivers[$namespace] = $nestedDriver;
    }

    /**
     * Gets the array of nested drivers.
     *
     * @return array<string, MappingDriver> $drivers
     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass(string $className, ClassMetadata $metadata)
    {
        foreach ($this->drivers as $namespace => $driver) {
            if (self::isMatchingNamespace($className, $namespace)) {
                $driver->loadMetadataForClass($className, $metadata);

                return;
            }
        }

        if ($this->defaultDriver !== null) {
            $this->defaultDriver->loadMetadataForClass($className, $metadata);

            return;
        }

        throw MappingException::classNotFoundInNamespaces($className, array_keys($this->drivers));
    }

    /**
     * {@inheritDoc}
     */
    public function getAllClassNames()
    {
        $classNames    = [];
        $driverClasses = [];

        foreach ($this->drivers as $namespace => $driver) {
            $oid = spl_object_hash($driver);

            if (! isset($driverClasses[$oid])) {
                $driverClasses[$oid] = $driver->getAllClassNames();
            }

            foreach ($driverClasses[$oid] as $className) {
                if (! self::isMatchingNamespace($className, $namespace)) {
                    continue;
                }

                $classNames[$className] = true;
            }
        }

        if ($this->defaultDriver !== null) {
            foreach ($this->defaultDriver->getAllClassNames() as $className) {
                $classNames[$className] = true;
            }
        }

        return array_keys($classNames);
    }

    /**
     * {@inheritDoc}
     */
    public function isTransient(string $className)
    {
        foreach ($this->drivers as $namespace => $driver) {
            if (self::isMatchingNamespace($className, $namespace)) {
                return $driver->isTransient($className);
            }
        }

        if ($this->defaultDriver !== null) {
            return $this->defaultDriver->isTransient($className);
        }

        return true;
    }

    /**
     * Checks if the given class name matches the namespace.
     */
    protected static function isMatchingNamespace(string $className, string $namespace): bool
    {
        return dirname($className) === trim($namespace, '\\');
    }
}
