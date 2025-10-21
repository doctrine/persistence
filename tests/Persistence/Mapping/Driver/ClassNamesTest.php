<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping\Driver;

use Doctrine\Persistence\Mapping\Driver\ClassNames;
use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Tests\Persistence\Mapping\_files\colocated\Entity;
use Doctrine\Tests\Persistence\Mapping\_files\colocated\EntityFixture;

class ClassNamesTest extends DoctrineTestCase
{
    public function testGetClassNames(): void
    {
        $classNames = [
            Entity::class,
            EntityFixture::class,
        ];

        $locator = new ClassNames($classNames);

        self::assertSame($classNames, $locator->getClassNames());
    }
}
