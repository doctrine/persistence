<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping\_files\colocated;

use Doctrine\Persistence\Mapping\ClassMetadata;

/**
 * The driver should include this file and return its class name
 * from {@see \Doctrine\Persistence\Mapping\Driver\ColocatedMappingDriver::getAllClassNames()} method.
 */
class Entity
{
    /** @phpstan-param ClassMetadata<object> $metadata */
    public static function loadMetadata(ClassMetadata $metadata): void
    {
        $metadata->getFieldNames();
    }
}
