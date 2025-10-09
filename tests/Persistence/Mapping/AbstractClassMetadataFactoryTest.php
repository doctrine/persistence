<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Persistence\Mapping\ReflectionService;
use Doctrine\Tests\DoctrineTestCase;
use RuntimeException;

final class AbstractClassMetadataFactoryTest extends DoctrineTestCase
{
    /** @param ClassMetadata<object>|null $metadata */
    private function createTestFactory(
        MappingDriver|null $driver = null,
        ClassMetadata|null $metadata = null,
    ): TestAbstractClassMetadataFactory {
        return new TestAbstractClassMetadataFactory($driver, $metadata);
    }

    public function testItSkipsTransientClasses(): void
    {
        $driver = $this->createMock(MappingDriver::class);
        $cmf    = $this->createTestFactory($driver);

        $metadataCallCount                     = 0;
        $cmf->newClassMetadataInstanceCallback = function ($className) use (&$metadataCallCount) {
            $metadataCallCount++;
            if ($metadataCallCount === 1) {
                self::assertEquals(SomeGrandParentEntity::class, $className);
            } elseif ($metadataCallCount === 2) {
                self::assertEquals(SomeEntity::class, $className);
            }

            return $this->createMock(ClassMetadata::class);
        };

        $driverCallCount = 0;
        $driver->expects(self::exactly(2))
            ->method('isTransient')
            ->willReturnCallback(static function ($className) use (&$driverCallCount) {
                $driverCallCount++;
                if ($driverCallCount === 1) {
                    self::assertEquals(SomeGrandParentEntity::class, $className);

                    return false;
                }

                if ($driverCallCount === 2) {
                    self::assertEquals(SomeParentEntity::class, $className);

                    return true;
                }
            });

        $cmf->getMetadataFor(SomeEntity::class);
    }

    public function testItThrowsWhenAttemptingToGetMetadataForAnonymousClass(): void
    {
        $cmf = $this->createTestFactory();
        $this->expectException(MappingException::class);
        $cmf->getMetadataFor((new class {
        })::class);
    }

    public function testAnonymousClassIsNotMistakenForShortAlias(): void
    {
        $driver = $this->createMock(MappingDriver::class);
        $driver->method('isTransient')->willReturn(false);
        $cmf = $this->createTestFactory($driver);

        self::assertFalse($cmf->isTransient((new class () {
        })::class));
    }

    public function testItThrowsWhenAttemptingToGetMetadataForShortAlias(): void
    {
        $cmf = $this->createTestFactory();
        $this->expectException(MappingException::class);
        // @phpstan-ignore-next-line
        $cmf->getMetadataFor('App:Test');
    }

    public function testItThrowsWhenAttemptingToCheckTransientForShortAlias(): void
    {
        $cmf = $this->createTestFactory();
        $this->expectException(MappingException::class);
        // @phpstan-ignore-next-line
        $cmf->isTransient('App:Test');
    }

    public function testItGetsTheSameMetadataForBackslashedClassName(): void
    {
        $driver = $this->createMock(MappingDriver::class);
        $cmf    = $this->createTestFactory($driver);

        $metadata                              = self::createStub(ClassMetadata::class);
        $cmf->newClassMetadataInstanceCallback = static function ($className) use ($metadata) {
            self::assertEquals(SomeOtherEntity::class, $className);

            return $metadata;
        };

        self::assertSame($cmf->getMetadataFor(SomeOtherEntity::class), $cmf->getMetadataFor('\\' . SomeOtherEntity::class));
    }
}

class SomeGrandParentEntity
{
}

class SomeParentEntity extends SomeGrandParentEntity
{
}

final class SomeEntity extends SomeParentEntity
{
}

final class SomeOtherEntity
{
}

/** @template-extends AbstractClassMetadataFactory<ClassMetadata<object>> */
class TestAbstractClassMetadataFactory extends AbstractClassMetadataFactory
{
    /** @var callable|null */
    public $newClassMetadataInstanceCallback;

    /** @param ClassMetadata<object>|null $defaultMetadata */
    public function __construct(
        private MappingDriver|null $driver = null,
        private ClassMetadata|null $defaultMetadata = null,
    ) {
    }

    protected function initialize(): void
    {
        $this->initialized = true;
    }

    protected function getDriver(): MappingDriver
    {
        return $this->driver ?? throw new RuntimeException('Driver not set');
    }

    protected function wakeupReflection(ClassMetadata $class, ReflectionService $reflService): void
    {
        // No-op for tests
    }

    protected function initializeReflection(ClassMetadata $class, ReflectionService $reflService): void
    {
        // No-op for tests
    }

    protected function isEntity(ClassMetadata $class): bool
    {
        return true;
    }

    /** @param list<class-string> $nonSuperclassParents */
    protected function doLoadMetadata(
        ClassMetadata $class,
        ClassMetadata|null $parent,
        bool $rootEntityFound,
        array $nonSuperclassParents,
    ): void {
        // No-op for tests - metadata loading is handled by driver
    }

    protected function newClassMetadataInstance(string $className): ClassMetadata
    {
        if ($this->newClassMetadataInstanceCallback !== null) {
            return ($this->newClassMetadataInstanceCallback)($className);
        }

        return $this->defaultMetadata ?? throw new RuntimeException('Default metadata not set');
    }
}
