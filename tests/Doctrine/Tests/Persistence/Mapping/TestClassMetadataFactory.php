<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\ReflectionService;

class TestClassMetadataFactory extends AbstractClassMetadataFactory
{
    /** @var MappingDriver */
    public $driver;

    /** @var ClassMetadata|null */
    public $metadata;

    /** @var callable|null */
    public $fallbackCallback;

    public function __construct(MappingDriver $driver, ?ClassMetadata $metadata)
    {
        $this->driver   = $driver;
        $this->metadata = $metadata;
    }

    /**
     * {@inheritDoc}
     */
    protected function doLoadMetadata($class, $parent, $rootEntityFound, array $nonSuperclassParents): void
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function getFqcnFromAlias($namespaceAlias, $simpleClassName)
    {
        return __NAMESPACE__ . '\\' . $simpleClassName;
    }

    protected function initialize(): void
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function newClassMetadataInstance($className)
    {
        return $this->metadata;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDriver()
    {
        return $this->driver;
    }

    protected function wakeupReflection(ClassMetadata $class, ReflectionService $reflService): void
    {
    }

    protected function initializeReflection(ClassMetadata $class, ReflectionService $reflService): void
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function isEntity(ClassMetadata $class)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function onNotFoundMetadata($className)
    {
        if (! $this->fallbackCallback) {
            return null;
        }

        return ($this->fallbackCallback)();
    }

    /**
     * {@inheritDoc}
     */
    public function isTransient($class): bool
    {
        $name = $this->metadata->getName();

        return $class !== $name;
    }
}
