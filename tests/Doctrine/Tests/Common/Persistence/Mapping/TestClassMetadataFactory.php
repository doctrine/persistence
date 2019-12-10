<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Persistence\Mapping;

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
     * @param string[] $nonSuperclassParents
     */
    protected function doLoadMetadata($class, $parent, $rootEntityFound, array $nonSuperclassParents)
    {
    }

    protected function getFqcnFromAlias($namespaceAlias, $simpleClassName)
    {
        return __NAMESPACE__ . '\\' . $simpleClassName;
    }

    protected function initialize()
    {
    }

    protected function newClassMetadataInstance($className)
    {
        return $this->metadata;
    }

    protected function getDriver()
    {
        return $this->driver;
    }

    protected function wakeupReflection(ClassMetadata $class, ReflectionService $reflService)
    {
    }

    protected function initializeReflection(ClassMetadata $class, ReflectionService $reflService)
    {
    }

    protected function isEntity(ClassMetadata $class)
    {
        return true;
    }

    protected function onNotFoundMetadata($className)
    {
        if (! $this->fallbackCallback) {
            return null;
        }

        return ($this->fallbackCallback)();
    }

    public function isTransient($class)
    {
        $name = $this->metadata->getName();

        return $class !== $name;
    }
}
