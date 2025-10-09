<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Tests\DoctrineTestCase;

final class AbstractClassMetadataFactoryTest extends DoctrineTestCase
{
    public function testItSkipsTransientClasses(): void
    {
        $cmf = $this->getMockForAbstractClass(AbstractClassMetadataFactory::class);

        $metadataCallCount = 0;
        $cmf
            ->method('newClassMetadataInstance')
            ->willReturnCallback(function ($className) use (&$metadataCallCount) {
                $metadataCallCount++;
                if ($metadataCallCount === 1) {
                    self::assertEquals(SomeGrandParentEntity::class, $className);
                } elseif ($metadataCallCount === 2) {
                    self::assertEquals(SomeEntity::class, $className);
                }

                return $this->createMock(ClassMetadata::class);
            });

        $driver = $this->createMock(MappingDriver::class);
        $cmf->method('getDriver')
            ->willReturn($driver);

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
        $cmf = $this->getMockForAbstractClass(AbstractClassMetadataFactory::class);
        $this->expectException(MappingException::class);
        $cmf->getMetadataFor((new class {
        })::class);
    }

    public function testAnonymousClassIsNotMistakenForShortAlias(): void
    {
        $cmf = $this->getMockForAbstractClass(AbstractClassMetadataFactory::class);

        self::assertFalse($cmf->isTransient((new class () {
        })::class));
    }

    public function testItThrowsWhenAttemptingToGetMetadataForShortAlias(): void
    {
        $cmf = $this->getMockForAbstractClass(AbstractClassMetadataFactory::class);
        $this->expectException(MappingException::class);
        // @phpstan-ignore-next-line
        $cmf->getMetadataFor('App:Test');
    }

    public function testItThrowsWhenAttemptingToCheckTransientForShortAlias(): void
    {
        $cmf = $this->getMockForAbstractClass(AbstractClassMetadataFactory::class);
        $this->expectException(MappingException::class);
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
                self::createStub(ClassMetadata::class),
            );

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
