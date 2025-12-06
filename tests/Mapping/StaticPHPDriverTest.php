<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\ClassNames;
use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver;
use PHPUnit\Framework\TestCase;

class StaticPHPDriverTest extends TestCase
{
    public function testLoadMetadata(): void
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::once())->method('getFieldNames');

        $driver = new StaticPHPDriver([__DIR__]);
        $driver->loadMetadataForClass(TestEntity::class, $metadata);
    }

    public function testGetAllClassNames(): void
    {
        $driver     = new StaticPHPDriver([__DIR__]);
        $classNames = $driver->getAllClassNames();

        self::assertContains(TestEntity::class, $classNames);
    }

    public function testGetAllClassesNamesWithClassLocator(): void
    {
        $driver     = new StaticPHPDriver(new ClassNames([TestEntity::class]));
        $classNames = $driver->getAllClassNames();

        self::assertSame([TestEntity::class], $classNames);
    }
}

class TestEntity
{
    /** @phpstan-param ClassMetadata<object> $metadata */
    public static function loadMetadata(ClassMetadata $metadata): void
    {
        $metadata->getFieldNames();
    }
}
