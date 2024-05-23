<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\ReflectionService;

/**
 * @template CMTemplate of ClassMetadata
 * @template-extends AbstractClassMetadataFactory<CMTemplate>
 */
class TestClassMetadataFactory extends AbstractClassMetadataFactory
{
    /** @var callable|null */
    public $fallbackCallback;

    /** @psalm-param CMTemplate $metadata */
    public function __construct(public MappingDriver $driver, public ClassMetadata $metadata)
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function doLoadMetadata(
        ClassMetadata $class,
        ClassMetadata|null $parent,
        bool $rootEntityFound,
        array $nonSuperclassParents,
    ): void {
    }

    protected function initialize(): void
    {
    }

    protected function newClassMetadataInstance(string $className): ClassMetadata
    {
        return $this->metadata;
    }

    protected function getDriver(): MappingDriver
    {
        return $this->driver;
    }

    protected function wakeupReflection(ClassMetadata $class, ReflectionService $reflService): void
    {
    }

    protected function initializeReflection(ClassMetadata $class, ReflectionService $reflService): void
    {
    }

    protected function isEntity(ClassMetadata $class): bool
    {
        return true;
    }

    protected function onNotFoundMetadata(string $className): ClassMetadata|null
    {
        if ($this->fallbackCallback === null) {
            return null;
        }

        return ($this->fallbackCallback)();
    }

    public function isTransient(string $className): bool
    {
        return $className !== $this->metadata->getName();
    }

    public function getCacheKey(string $realClassName): string
    {
        return parent::getCacheKey($realClassName);
    }
}
