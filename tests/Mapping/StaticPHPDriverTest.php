<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\ClassNames;
use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\Tests\Persistence\Mapping\_files\colocated\Entity;
use PHPUnit\Framework\TestCase;

class StaticPHPDriverTest extends TestCase
{
    public function testLoadMetadata(): void
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::once())->method('getFieldNames');

        $driver = new StaticPHPDriver([]);
        $driver->loadMetadataForClass(Entity::class, $metadata);
    }

    public function testGetAllClassNames(): void
    {
        $driver     = new StaticPHPDriver([__DIR__ . '/_files/colocated/']);
        $classNames = $driver->getAllClassNames();

        self::assertContains(Entity::class, $classNames);
    }

    public function testGetAllClassesNamesWithClassLocator(): void
    {
        $driver     = new StaticPHPDriver(new ClassNames([Entity::class]));
        $classNames = $driver->getAllClassNames();

        self::assertSame([Entity::class], $classNames);
    }
}
