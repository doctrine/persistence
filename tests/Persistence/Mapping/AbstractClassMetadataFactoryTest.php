<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Tests\DoctrineTestCase;

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
