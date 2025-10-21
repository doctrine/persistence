<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping\_files\colocated;

/**
 * This class is considered transient {@see \Doctrine\Persistence\Mapping\Driver\ColocatedMappingDriver::isTransient()}
 * by the driver, and therefore its class name is not returned.
 */
class TestClass
{
}
