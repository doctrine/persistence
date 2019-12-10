<?php

namespace Doctrine\Common\Persistence;

use const E_USER_DEPRECATED;
use function class_alias;
use function interface_exists;
use function sprintf;
use function trigger_error;

if (! interface_exists(\Doctrine\Persistence\ObjectManager::class, false)) {
    @trigger_error(sprintf(
        'The %s\ObjectManager class is deprecated since doctrine/persistence 1.3 and will be removed in 2.0.'
        . ' Use \Doctrine\Persistence\ObjectManager instead.',
        __NAMESPACE__
    ), E_USER_DEPRECATED);
}

class_alias(
    \Doctrine\Persistence\ObjectManager::class,
    __NAMESPACE__ . '\ObjectManager'
);

if (false) {
    /**
     * @deprecated 1.3 Use Doctrine\Persistence\ObjectManager
     */
    interface ObjectManager extends \Doctrine\Persistence\ObjectManager
    {
    }
}
