<?php

namespace Doctrine\Common\Persistence\Event;

use const E_USER_DEPRECATED;
use function class_alias;
use function class_exists;
use function sprintf;
use function trigger_error;

if (! class_exists(\Doctrine\Persistence\Event\ManagerEventArgs::class, false)) {
    @trigger_error(sprintf(
        'The %s\ManagerEventArgs class is deprecated since doctrine/persistence 1.3 and will be removed in 2.0.'
        . ' Use \Doctrine\Persistence\Event\ManagerEventArgs instead.',
        __NAMESPACE__
    ), E_USER_DEPRECATED);
}

class_alias(
    \Doctrine\Persistence\Event\ManagerEventArgs::class,
    __NAMESPACE__ . '\ManagerEventArgs'
);

if (false) {
    /**
     * @deprecated 1.3 Use Doctrine\Persistence\Event\ManagerEventArgs
     */
    class ManagerEventArgs extends \Doctrine\Persistence\Event\ManagerEventArgs
    {
    }
}
