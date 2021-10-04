<?php

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\Deprecations\PHPUnit\VerifyDeprecations;
use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Tests\DoctrineTestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionMethod;
use stdClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

use function assert;
use function class_exists;

/**
 * @covers \Doctrine\Persistence\Mapping\AbstractClassMetadataFactory
 */
class ClassMetadataFactoryTest extends DoctrineTestCase
{
    use VerifyDeprecations;

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

    public function testSetGetCacheDriver(): void
    {
        self::assertNull($this->cmf->getCacheDriver());
        self::assertNull(self::getCache($this->cmf));

        $cache = $this->getArrayCache();
        $this->cmf->setCacheDriver($cache);

        self::assertSame($cache, $this->cmf->getCacheDriver());
        self::assertInstanceOf(CacheItemPoolInterface::class, self::getCache($this->cmf));

        $this->cmf->setCacheDriver(null);
        self::assertNull($this->cmf->getCacheDriver());
        self::assertNull(self::getCache($this->cmf));
    }

    public function testSetGetCache(): void
    {
        self::assertNull(self::getCache($this->cmf));
        self::assertNull($this->cmf->getCacheDriver());

        $cache = new ArrayAdapter();
        $this->cmf->setCache($cache);
        self::assertSame($cache, self::getCache($this->cmf));
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
        $this->expectDeprecationWithIdentifier('https://github.com/doctrine/persistence/issues/204');

        $this->cmf->getMetadataFor('prefix:ChildEntity');

        self::assertTrue($this->cmf->hasMetadataFor(__NAMESPACE__ . '\ChildEntity'));
        self::assertTrue($this->cmf->hasMetadataFor('prefix:ChildEntity'));
    }

    /**
     * @group DCOM-270
     */
    public function testGetInvalidAliasedMetadata(): void
    {
        $this->expectDeprecationWithIdentifier('https://github.com/doctrine/persistence/issues/204');

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

        self::assertSame($classMetadata, $this->cmf->getMetadataFor('Foo'));
    }

    public function testWillFailOnFallbackFailureWithNotLoadedMetadata(): void
    {
        $this->cmf->fallbackCallback = static function () {
            return null;
        };

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

        self::assertSame($metadata, $this->cmf->getMetadataFor('Foo'));
    }

    /**
     * @psalm-param AbstractClassMetadataFactory<ClassMetadata<object>> $classMetadataFactory
     */
    private static function getCache(AbstractClassMetadataFactory $classMetadataFactory): ?CacheItemPoolInterface
    {
        $method = new ReflectionMethod($classMetadataFactory, 'getCache');
        $method->setAccessible(true);

        return $method->invoke($classMetadataFactory);
    }

    private function getArrayCache(): Cache
    {
        $cache = class_exists(DoctrineProvider::class)
            ? DoctrineProvider::wrap(new ArrayAdapter())
            : new ArrayCache();
        assert($cache instanceof Cache);

        return $cache;
    }
}

class RootEntity
{
}

class ChildEntity extends RootEntity
{
}
