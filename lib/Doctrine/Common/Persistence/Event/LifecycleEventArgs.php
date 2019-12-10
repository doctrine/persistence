<?php

namespace Doctrine\Common\Persistence\Event;

use const E_USER_DEPRECATED;
use function class_alias;
use function class_exists;
use function sprintf;
use function trigger_error;

if (! class_exists(\Doctrine\Persistence\Event\LifecycleEventArgs::class, false)) {
    @trigger_error(sprintf(
        'The %s\LifecycleEventArgs class is deprecated since doctrine/persistence 1.3 and will be removed in 2.0.'
        . ' Use \Doctrine\Persistence\Event\LifecycleEventArgs instead.',
        __NAMESPACE__
    ), E_USER_DEPRECATED);
}

class_alias(
    \Doctrine\Persistence\Event\LifecycleEventArgs::class,
    __NAMESPACE__ . '\LifecycleEventArgs'
);

if (false) {
    /**
     * @deprecated 1.3 Use Doctrine\Persistence\Event\LifecycleEventArgs
     */
    class LifecycleEventArgs extends \Doctrine\Persistence\Event\LifecycleEventArgs
    {
    }
}
