<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\MappingException;

use function array_keys;
use function spl_object_hash;
use function str_starts_with;

/**
 * The DriverChain allows you to add multiple other mapping drivers for
 * certain namespaces.
 */
class MappingDriverChain implements MappingDriver
{
    /**
     * The default driver.
     */
    private MappingDriver|null $defaultDriver = null;

    /** @var array<string, MappingDriver> */
    private array $drivers = [];

    /** Gets the default driver. */
    public function getDefaultDriver(): MappingDriver|null
    {
        return $this->defaultDriver;
    }

    /** Set the default driver. */
    public function setDefaultDriver(MappingDriver $driver): void
    {
        $this->defaultDriver = $driver;
    }

    /** Adds a nested driver. */
    public function addDriver(MappingDriver $nestedDriver, string $namespace): void
    {
        $this->drivers[$namespace] = $nestedDriver;
    }

    /**
     * Gets the array of nested drivers.
     *
     * @return array<string, MappingDriver> $drivers
     */
    public function getDrivers(): array
    {
        return $this->drivers;
    }

    public function loadMetadataForClass(string $className, ClassMetadata $metadata): void
    {
        foreach ($this->drivers as $namespace => $driver) {
            if (str_starts_with($className, $namespace)) {
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
    public function getAllClassNames(): array
    {
        $classNames    = [];
        $driverClasses = [];

        foreach ($this->drivers as $namespace => $driver) {
            $oid = spl_object_hash($driver);

            if (! isset($driverClasses[$oid])) {
                $driverClasses[$oid] = $driver->getAllClassNames();
            }

            foreach ($driverClasses[$oid] as $className) {
                if (! str_starts_with($className, $namespace)) {
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

    public function isTransient(string $className): bool
    {
        foreach ($this->drivers as $namespace => $driver) {
            if (str_starts_with($className, $namespace)) {
                return $driver->isTransient($className);
            }
        }

        if ($this->defaultDriver !== null) {
            return $this->defaultDriver->isTransient($className);
        }

        return true;
    }
}
