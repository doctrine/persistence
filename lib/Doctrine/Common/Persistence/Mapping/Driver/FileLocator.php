<?php

namespace Doctrine\Common\Persistence\Mapping\Driver;

use const E_USER_DEPRECATED;
use function class_alias;
use function interface_exists;
use function sprintf;
use function trigger_error;

if (! interface_exists(\Doctrine\Persistence\Mapping\Driver\FileLocator::class, false)) {
    @trigger_error(sprintf(
        'The %s\FileLocator class is deprecated since doctrine/persistence 1.3 and will be removed in 2.0.'
        . ' Use \Doctrine\Persistence\Mapping\Driver\FileLocator instead.',
        __NAMESPACE__
    ), E_USER_DEPRECATED);
}

class_alias(
    \Doctrine\Persistence\Mapping\Driver\FileLocator::class,
    __NAMESPACE__ . '\FileLocator'
);

if (false) {
    /**
     * @deprecated 1.3 Use Doctrine\Persistence\Mapping\Driver\FileLocator
     */
    interface FileLocator extends \Doctrine\Persistence\Mapping\Driver\FileLocator
    {
    }
}
