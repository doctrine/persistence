<?php

namespace Doctrine\Common\Persistence\Mapping\Driver;

use const E_USER_DEPRECATED;
use function class_alias;
use function interface_exists;
use function sprintf;
use function trigger_error;

if (! interface_exists(\Doctrine\Persistence\Mapping\Driver\MappingDriver::class, false)) {
    @trigger_error(sprintf(
        'The %s\MappingDriver class is deprecated since doctrine/persistence 1.3 and will be removed in 2.0.'
        . ' Use \Doctrine\Persistence\Mapping\Driver\MappingDriver instead.',
        __NAMESPACE__
    ), E_USER_DEPRECATED);
}

class_alias(
    \Doctrine\Persistence\Mapping\Driver\MappingDriver::class,
    __NAMESPACE__ . '\MappingDriver'
);

if (false) {
    /**
     * @deprecated 1.3 Use Doctrine\Persistence\Mapping\Driver\MappingDriver
     */
    interface MappingDriver extends \Doctrine\Persistence\Mapping\Driver\MappingDriver
    {
    }
}
