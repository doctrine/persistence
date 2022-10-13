<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Tests\DoctrineTestCase;
use Foo;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionMethod;
use stdClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/** @covers \Doctrine\Persistence\Mapping\AbstractClassMetadataFactory */
class ClassMetadataFactoryTest extends DoctrineTestCase
{
    /**
     * @var TestClassMetadataFactory
     * @psalm-var TestClassMetadataFactory<ClassMetadata<object>>
     */
    private $cmf;

    protected function setUp(): void
    {
        $driver = $this->createMock(MappingDriver::class);

        /** @psalm-var ClassMetadata<object> */
        $metadata  = $this->createMock(ClassMetadata::class);
        $this->cmf = new TestClassMetadataFactory($driver, $metadata);
    }

    public function testSetGetCache(): void
    {
        self::assertNull(self::getCache($this->cmf));

        $cache = new ArrayAdapter();
        $this->cmf->setCache($cache);
        self::assertSame($cache, self::getCache($this->cmf));
    }

    public function testGetMetadataFor(): void
    {
        $metadata = $this->cmf->getMetadataFor(stdClass::class);

        self::assertTrue($this->cmf->hasMetadataFor(stdClass::class));
    }

    public function testGetMetadataForAbsentClass(): void
    {
        $this->expectException(MappingException::class);
        $this->cmf->getMetadataFor(Foo::class);
    }

    public function testGetParentMetadata(): void
    {
        $metadata = $this->cmf->getMetadataFor(ChildEntity::class);

        self::assertTrue($this->cmf->hasMetadataFor(ChildEntity::class));
        self::assertTrue($this->cmf->hasMetadataFor(RootEntity::class));
    }

    public function testGetCachedMetadata(): void
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $cache    = new ArrayAdapter();
        $item     = $cache->getItem($this->cmf->getCacheKey(ChildEntity::class));
        $item->set($metadata);
        $cache->save($item);

        $this->cmf->setCache($cache);

        self::assertEquals($metadata, $this->cmf->getMetadataFor(ChildEntity::class));
    }

    public function testCacheGetMetadataFor(): void
    {
        $cache = new ArrayAdapter();
        $this->cmf->setCache($cache);

        $loadedMetadata = $this->cmf->getMetadataFor(ChildEntity::class);

        $item = $cache->getItem($this->cmf->getCacheKey(ChildEntity::class));
        self::assertTrue($item->isHit());
        self::assertEquals($loadedMetadata, $item->get());
    }

    public function testWillFallbackOnNotLoadedMetadata(): void
    {
        $classMetadata = $this->createMock(ClassMetadata::class);

        $this->cmf->fallbackCallback = static function () use ($classMetadata) {
            return $classMetadata;
        };

        self::assertSame($classMetadata, $this->cmf->getMetadataFor(Foo::class));
    }

    public function testWillFailOnFallbackFailureWithNotLoadedMetadata(): void
    {
        $this->cmf->fallbackCallback = static function () {
            return null;
        };

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("Class 'Foo' does not exist");

        $this->cmf->getMetadataFor(Foo::class);
    }

    /** @group 717 */
    public function testWillIgnoreCacheEntriesThatAreNotMetadataInstances(): void
    {
        $key = $this->cmf->getCacheKey(RootEntity::class);

        $metadata = $this->cmf->metadata;

        $item = $this->createMock(CacheItemInterface::class);

        $item
            ->method('getKey')
            ->willReturn($key);
        $item
            ->method('get')
            ->willReturn(new stdClass());
        $item
            ->expects(self::once())
            ->method('set')
            ->with($metadata);

        $cacheDriver = $this->createMock(CacheItemPoolInterface::class);
        $cacheDriver
            ->method('getItem')
            ->with($key)
            ->willReturn($item);
        $cacheDriver
            ->expects(self::once())
            ->method('getItems')
            ->with([$key])
            ->willReturn([$item]);
        $cacheDriver
            ->expects(self::once())
            ->method('saveDeferred')
            ->with($item);
        $cacheDriver
            ->expects(self::once())
            ->method('commit');

        $this->cmf->setCache($cacheDriver);

        self::assertSame($metadata, $this->cmf->getMetadataFor(RootEntity::class));
    }

    public function testWillNotCacheFallbackMetadata(): void
    {
        $key = $this->cmf->getCacheKey('Foo');

        $metadata = $this->cmf->metadata;

        $item = $this->createMock(CacheItemInterface::class);

        $item
            ->method('get')
            ->willReturn(null);
        $item
            ->expects(self::never())
            ->method('set');

        $cacheDriver = $this->createMock(CacheItemPoolInterface::class);
        $cacheDriver
            ->expects(self::once())
            ->method('getItem')
            ->with($key)
            ->willReturn($item);
        $cacheDriver
            ->expects(self::never())
            ->method('saveDeferred');
        $cacheDriver
            ->expects(self::never())
            ->method('commit');

        $this->cmf->setCache($cacheDriver);

        $this->cmf->fallbackCallback = static function () use ($metadata): ClassMetadata {
            return $metadata;
        };

        self::assertSame($metadata, $this->cmf->getMetadataFor(Foo::class));
    }

    /** @psalm-param AbstractClassMetadataFactory<ClassMetadata<object>> $classMetadataFactory */
    private static function getCache(AbstractClassMetadataFactory $classMetadataFactory): ?CacheItemPoolInterface
    {
        $method = new ReflectionMethod($classMetadataFactory, 'getCache');
        $method->setAccessible(true);

        return $method->invoke($classMetadataFactory);
    }
}

class RootEntity
{
}

class ChildEntity extends RootEntity
{
}
