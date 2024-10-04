<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Tests\DoctrineTestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

use function get_class;

final class AbstractClassMetadataFactoryTest extends DoctrineTestCase
{
    public function testItSkipsTransientClasses(): void
    {
        $cmf = $this->getMockForAbstractClass(AbstractClassMetadataFactory::class);
        $cmf
            ->method('newClassMetadataInstance')
            ->withConsecutive([SomeGrandParentEntity::class], [SomeEntity::class])
            ->willReturnOnConsecutiveCalls(
                $this->createMock(ClassMetadata::class),
                $this->createMock(ClassMetadata::class)
            );
        $driver = $this->createMock(MappingDriver::class);
        $cmf->method('getDriver')
            ->willReturn($driver);

        $driver->expects(self::exactly(2))
            ->method('isTransient')
            ->withConsecutive(
                [SomeGrandParentEntity::class],
                [SomeParentEntity::class]
            )
            ->willReturnOnConsecutiveCalls(false, true);

        $cmf->getMetadataFor(SomeEntity::class);
    }

    public function testItThrowsWhenAttemptingToGetMetadataForAnonymousClass(): void
    {
        $cmf = $this->getMockForAbstractClass(AbstractClassMetadataFactory::class);
        $this->expectException(MappingException::class);
        $cmf->getMetadataFor(get_class(new class {
        }));
    }

    public function testAnonymousClassIsNotMistakenForShortAlias(): void
    {
        $cmf = $this->getMockForAbstractClass(AbstractClassMetadataFactory::class);

        self::assertFalse($cmf->isTransient(get_class(new class () {
        })));
    }

    public function testItThrowsWhenAttemptingToGetMetadataForShortAlias(): void
    {
        $cmf = $this->getMockForAbstractClass(AbstractClassMetadataFactory::class);
        $this->expectException(MappingException::class);
        /**
         * @psalm-suppress ArgumentTypeCoercion
         * @psalm-suppress UndefinedClass
         */
        // @phpstan-ignore-next-line
        $cmf->getMetadataFor('App:Test');
    }

    public function testItThrowsWhenAttemptingToCheckTransientForShortAlias(): void
    {
        $cmf = $this->getMockForAbstractClass(AbstractClassMetadataFactory::class);
        $this->expectException(MappingException::class);
        /**
         * @psalm-suppress ArgumentTypeCoercion
         * @psalm-suppress UndefinedClass
         */
        // @phpstan-ignore-next-line
        $cmf->isTransient('App:Test');
    }

    public function testItGetsTheSameMetadataForBackslashedClassName(): void
    {
        $cmf = $this->getMockForAbstractClass(AbstractClassMetadataFactory::class);
        $cmf
            ->method('newClassMetadataInstance')
            ->with(SomeOtherEntity::class)
            ->willReturn(
                $this->createStub(ClassMetadata::class)
            );

        /** @psalm-suppress ArgumentTypeCoercion */
        self::assertSame($cmf->getMetadataFor(SomeOtherEntity::class), $cmf->getMetadataFor('\\' . SomeOtherEntity::class));
    }

    public function testCacheStoredWithPrefixedKeys(): void
    {
        $cmf = $this->getMockForAbstractClass(AbstractClassMetadataFactory::class);
        $cmf
            ->method('newClassMetadataInstance')
            ->with(SomeOtherEntity::class)
            ->willReturn(
                $this->createStub(ClassMetadata::class)
            );

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cmf->setCache($cache);

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('getKey')->willReturn('prefix__Doctrine__Tests__Persistence__Mapping__SomeOtherEntity__CLASSMETADATA__'); //Cache item's key is prefixed
        $cache->method('getItem')
            ->with('Doctrine__Tests__Persistence__Mapping__SomeOtherEntity__CLASSMETADATA__') //Key which is generated from class name is not prefixed
            ->willReturn($cacheItem);

        $cacheItem->expects(self::once())->method('set');
        $cache->expects(self::once())->method('saveDeferred');

        $cmf->getMetadataFor(SomeOtherEntity::class);
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
