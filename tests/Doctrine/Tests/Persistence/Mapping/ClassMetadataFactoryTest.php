<?php

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Tests\DoctrineTestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use stdClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\DoctrineAdapter;
use Symfony\Component\Cache\DoctrineProvider;

/**
 * @covers \Doctrine\Persistence\Mapping\AbstractClassMetadataFactory
 */
class ClassMetadataFactoryTest extends DoctrineTestCase
{
    /** @var TestClassMetadataFactory */
    private $cmf;

    protected function setUp(): void
    {
        $driver    = $this->createMock(MappingDriver::class);
        $metadata  = $this->createMock(ClassMetadata::class);
        $this->cmf = new TestClassMetadataFactory($driver, $metadata);
    }

    public function testSetGetCacheDriver(): void
    {
        self::assertNull($this->cmf->getCacheDriver());
        self::assertNull($this->cmf->getCache());

        $cache = new ArrayCache();
        $this->cmf->setCacheDriver($cache);

        self::assertSame($cache, $this->cmf->getCacheDriver());
        self::assertInstanceOf(DoctrineAdapter::class, $this->cmf->getCache());

        $this->cmf->setCacheDriver(null);
        self::assertNull($this->cmf->getCacheDriver());
        self::assertNull($this->cmf->getCache());
    }

    public function testSetGetCache(): void
    {
        self::assertNull($this->cmf->getCache());
        self::assertNull($this->cmf->getCacheDriver());

        $cache = new ArrayAdapter();
        $this->cmf->setCache($cache);
        self::assertSame($cache, $this->cmf->getCache());
        self::assertInstanceOf(DoctrineProvider::class, $this->cmf->getCacheDriver());
    }

    public function testGetMetadataFor(): void
    {
        $metadata = $this->cmf->getMetadataFor('stdClass');

        self::assertInstanceOf(ClassMetadata::class, $metadata);
        self::assertTrue($this->cmf->hasMetadataFor('stdClass'));
    }

    public function testGetMetadataForAbsentClass(): void
    {
        $this->expectException(MappingException::class);
        $this->cmf->getMetadataFor(__NAMESPACE__ . '\AbsentClass');
    }

    public function testGetParentMetadata(): void
    {
        $metadata = $this->cmf->getMetadataFor(ChildEntity::class);

        self::assertInstanceOf(ClassMetadata::class, $metadata);
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

    public function testGetAliasedMetadata(): void
    {
        $this->cmf->getMetadataFor('prefix:ChildEntity');

        self::assertTrue($this->cmf->hasMetadataFor(__NAMESPACE__ . '\ChildEntity'));
        self::assertTrue($this->cmf->hasMetadataFor('prefix:ChildEntity'));
    }

    /**
     * @group DCOM-270
     */
    public function testGetInvalidAliasedMetadata(): void
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessage(
            'Class \'Doctrine\Tests\Persistence\Mapping\ChildEntity:Foo\' does not exist'
        );

        $this->cmf->getMetadataFor('prefix:ChildEntity:Foo');
    }

    /**
     * @group DCOM-270
     */
    public function testClassIsTransient(): void
    {
        self::assertTrue($this->cmf->isTransient('prefix:ChildEntity:Foo'));
    }

    public function testWillFallbackOnNotLoadedMetadata(): void
    {
        $classMetadata = $this->createMock(ClassMetadata::class);

        $this->cmf->fallbackCallback = static function () use ($classMetadata) {
            return $classMetadata;
        };

        $this->cmf->metadata = null;

        self::assertSame($classMetadata, $this->cmf->getMetadataFor('Foo'));
    }

    public function testWillFailOnFallbackFailureWithNotLoadedMetadata(): void
    {
        $this->cmf->fallbackCallback = static function () {
            return null;
        };

        $this->cmf->metadata = null;

        $this->expectException(MappingException::class);

        $this->cmf->getMetadataFor('Foo');
    }

    /**
     * @group 717
     */
    public function testWillIgnoreCacheEntriesThatAreNotMetadataInstances(): void
    {
        $key = $this->cmf->getCacheKey(RootEntity::class);

        $metadata = $this->cmf->metadata;

        $item = $this->createMock(CacheItemInterface::class);

        $item
            ->expects(self::any())
            ->method('getKey')
            ->willReturn($key);
        $item
            ->expects(self::any())
            ->method('get')
            ->willReturn(new stdClass());
        $item
            ->expects(self::once())
            ->method('set')
            ->with($metadata);

        $cacheDriver = $this->createMock(CacheItemPoolInterface::class);
        $cacheDriver
            ->expects(self::any())
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
            ->expects(self::any())
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

        $fallbackCallback = $this->getMockBuilder(stdClass::class)->setMethods(['__invoke'])->getMock();

        $fallbackCallback
            ->expects(self::any())
            ->method('__invoke')
            ->willReturn($metadata);

        $this->cmf->fallbackCallback = $fallbackCallback;

        self::assertSame($metadata, $this->cmf->getMetadataFor('Foo'));
    }
}

class RootEntity
{
}

class ChildEntity extends RootEntity
{
}
