<?php

namespace Doctrine\Common\Persistence\Event;

use const E_USER_DEPRECATED;
use function class_alias;
use function class_exists;
use function sprintf;
use function trigger_error;

if (! class_exists(\Doctrine\Persistence\Event\OnClearEventArgs::class, false)) {
    @trigger_error(sprintf(
        'The %s\OnClearEventArgs class is deprecated since doctrine/persistence 1.3 and will be removed in 2.0.'
        . ' Use \Doctrine\Persistence\Event\OnClearEventArgs instead.',
        __NAMESPACE__
    ), E_USER_DEPRECATED);
}

class_alias(
    \Doctrine\Persistence\Event\OnClearEventArgs::class,
    __NAMESPACE__ . '\OnClearEventArgs'
);

if (false) {
    /**
     * @deprecated 1.3 Use Doctrine\Persistence\Event\OnClearEventArgs
     */
    class OnClearEventArgs extends \Doctrine\Persistence\Event\OnClearEventArgs
    {
    }
}
