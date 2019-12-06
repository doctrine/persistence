<?php

namespace Doctrine\Common\Persistence\Event;

use const E_USER_DEPRECATED;
use function class_alias;
use function class_exists;
use function sprintf;
use function trigger_error;

if (! class_exists(\Doctrine\Persistence\Event\PreUpdateEventArgs::class, false)) {
    @trigger_error(sprintf(
        'The %s\PreUpdateEventArgs class is deprecated since doctrine/persistence 1.3 and will be removed in 2.0.'
        . ' Use \Doctrine\Persistence\Event\PreUpdateEventArgs instead.',
        __NAMESPACE__
    ), E_USER_DEPRECATED);
}

class_alias(
    \Doctrine\Persistence\Event\PreUpdateEventArgs::class,
    __NAMESPACE__ . '\PreUpdateEventArgs'
);

if (false) {
    /**
     * @deprecated 1.3 Use Doctrine\Persistence\Event\PreUpdateEventArgs
     */
    class PreUpdateEventArgs extends \Doctrine\Persistence\Event\PreUpdateEventArgs
    {
    }
}
