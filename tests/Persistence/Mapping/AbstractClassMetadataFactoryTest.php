<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Deprecations\PHPUnit\VerifyDeprecations;
use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Tests\DoctrineTestCase;

final class AbstractClassMetadataFactoryTest extends DoctrineTestCase
{
    use VerifyDeprecations;

    public function testSetCacheDriverIsDeprecated(): void
    {
        $this->expectDeprecationWithIdentifier(
            'https://github.com/doctrine/persistence/issues/184'
        );

        $cmf = $this->getMockForAbstractClass(AbstractClassMetadataFactory::class);
        $cmf->setCacheDriver(null);
    }

    public function testGetCacheDriverIsDeprecated(): void
    {
        $this->expectDeprecationWithIdentifier(
            'https://github.com/doctrine/persistence/issues/184'
        );

        $cmf = $this->getMockForAbstractClass(AbstractClassMetadataFactory::class);
        $cmf->getCacheDriver();
    }

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

        $driver->expects($this->exactly(2))
            ->method('isTransient')
            ->withConsecutive(
                [SomeGrandParentEntity::class],
                [SomeParentEntity::class]
            )
            ->willReturnOnConsecutiveCalls(false, true);

        $cmf->getMetadataFor(SomeEntity::class);
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
