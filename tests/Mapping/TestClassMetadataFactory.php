<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\ReflectionService;
use Override;

/**
 * @template CMTemplate of ClassMetadata
 * @template-extends AbstractClassMetadataFactory<CMTemplate>
 */
class TestClassMetadataFactory extends AbstractClassMetadataFactory
{
    /** @var callable|null */
    public $fallbackCallback;

    /** @phpstan-param CMTemplate $metadata */
    public function __construct(public MappingDriver $driver, public ClassMetadata $metadata)
    {
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function doLoadMetadata(
        ClassMetadata $class,
        ClassMetadata|null $parent,
        bool $rootEntityFound,
        array $nonSuperclassParents,
    ): void {
    }

    #[Override]
    protected function initialize(): void
    {
    }

    #[Override]
    protected function newClassMetadataInstance(string $className): ClassMetadata
    {
        return $this->metadata;
    }

    #[Override]
    protected function getDriver(): MappingDriver
    {
        return $this->driver;
    }

    #[Override]
    protected function wakeupReflection(ClassMetadata $class, ReflectionService $reflService): void
    {
    }

    #[Override]
    protected function initializeReflection(ClassMetadata $class, ReflectionService $reflService): void
    {
    }

    #[Override]
    protected function isEntity(ClassMetadata $class): bool
    {
        return true;
    }

    #[Override]
    protected function onNotFoundMetadata(string $className): ClassMetadata|null
    {
        if ($this->fallbackCallback === null) {
            return null;
        }

        return ($this->fallbackCallback)();
    }

    #[Override]
    public function isTransient(string $className): bool
    {
        return $className !== $this->metadata->getName();
    }

    #[Override]
    public function getCacheKey(string $realClassName): string
    {
        return parent::getCacheKey($realClassName);
    }
}
