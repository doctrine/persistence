<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Tests\DoctrineTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

/**
 * @covers \Doctrine\Persistence\Mapping\AbstractClassMetadataFactory
 */
class ClassMetadataFactoryTest extends DoctrineTestCase
{
    /** @var TestClassMetadataFactory */
    private $cmf;

    protected function setUp() : void
    {
        $driver    = $this->createMock(MappingDriver::class);
        $metadata  = $this->createMock(ClassMetadata::class);
        $this->cmf = new TestClassMetadataFactory($driver, $metadata);
    }

    public function testGetCacheDriver() : void
    {
        self::assertNull($this->cmf->getCacheDriver());

        $cache = new ArrayCache();

        $this->cmf->setCacheDriver($cache);

        /** @var ArrayCache $cacheDriver */
        $cacheDriver = $this->cmf->getCacheDriver();

        self::assertSame($cache, $cacheDriver);
    }

    public function testGetMetadataFor() : void
    {
        $metadata = $this->cmf->getMetadataFor('stdClass');

        self::assertTrue($this->cmf->hasMetadataFor('stdClass'));
    }

    public function testGetMetadataForAbsentClass() : void
    {
        $this->expectException(MappingException::class);
        $this->cmf->getMetadataFor(__NAMESPACE__ . '\AbsentClass');
    }

    public function testGetParentMetadata() : void
    {
        $metadata = $this->cmf->getMetadataFor(ChildEntity::class);

        self::assertTrue($this->cmf->hasMetadataFor(ChildEntity::class));
        self::assertTrue($this->cmf->hasMetadataFor(RootEntity::class));
    }

    public function testGetCachedMetadata() : void
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $cache    = new ArrayCache();
        $cache->save(ChildEntity::class . '$CLASSMETADATA', $metadata);

        $this->cmf->setCacheDriver($cache);

        self::assertSame($metadata, $this->cmf->getMetadataFor(ChildEntity::class));
    }

    public function testCacheGetMetadataFor() : void
    {
        $cache = new ArrayCache();
        $this->cmf->setCacheDriver($cache);

        $loadedMetadata = $this->cmf->getMetadataFor(ChildEntity::class);

        self::assertSame($loadedMetadata, $cache->fetch(ChildEntity::class . '$CLASSMETADATA'));
    }

    public function testGetAliasedMetadata() : void
    {
        $this->cmf->getMetadataFor('prefix:ChildEntity');

        self::assertTrue($this->cmf->hasMetadataFor(__NAMESPACE__ . '\ChildEntity'));
        self::assertTrue($this->cmf->hasMetadataFor('prefix:ChildEntity'));
    }

    /**
     * @group DCOM-270
     */
    public function testGetInvalidAliasedMetadata() : void
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
    public function testClassIsTransient() : void
    {
        self::assertTrue($this->cmf->isTransient('prefix:ChildEntity:Foo'));
    }

    public function testWillFallbackOnNotLoadedMetadata() : void
    {
        $classMetadata = $this->createMock(ClassMetadata::class);

        $this->cmf->fallbackCallback = static function () use ($classMetadata) {
            return $classMetadata;
        };

        /** @var ClassMetadata $fooClassMetadata */
        $fooClassMetadata = $this->cmf->getMetadataFor('Foo');

        self::assertSame($classMetadata, $fooClassMetadata);
    }

    public function testWillFailOnFallbackFailureWithNotLoadedMetadata() : void
    {
        $this->cmf->fallbackCallback = static function () {
            return null;
        };

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("Class 'Foo' does not exist");

        $this->cmf->getMetadataFor('Foo');
    }

    /**
     * @group 717
     */
    public function testWillIgnoreCacheEntriesThatAreNotMetadataInstances() : void
    {
        /** @var Cache|MockObject $cacheDriver */
        $cacheDriver = $this->createMock(Cache::class);

        $this->cmf->setCacheDriver($cacheDriver);

        $cacheDriver->expects(self::once())->method('fetch')->with('Foo$CLASSMETADATA')->willReturn(new stdClass());

        /** @var ClassMetadata $metadata */
        $metadata = $this->createMock(ClassMetadata::class);

        /** @var MockObject|stdClass|callable $fallbackCallback */
        $fallbackCallback = $this->getMockBuilder(stdClass::class)->setMethods(['__invoke'])->getMock();

        $fallbackCallback->expects(self::any())->method('__invoke')->willReturn($metadata);

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
