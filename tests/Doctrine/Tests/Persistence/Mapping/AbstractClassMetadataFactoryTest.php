<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Deprecations\PHPUnit\VerifyDeprecations;
use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
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
}
