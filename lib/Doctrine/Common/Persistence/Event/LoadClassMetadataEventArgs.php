<?php

namespace Doctrine\Common\Persistence\Event;

use const E_USER_DEPRECATED;
use function class_alias;
use function class_exists;
use function sprintf;
use function trigger_error;

if (! class_exists(\Doctrine\Persistence\Event\LoadClassMetadataEventArgs::class, false)) {
    @trigger_error(sprintf(
        'The %s\LoadClassMetadataEventArgs class is deprecated since doctrine/persistence 1.3 and will be removed in 2.0.'
        . ' Use \Doctrine\Persistence\Event\LoadClassMetadataEventArgs instead.',
        __NAMESPACE__
    ), E_USER_DEPRECATED);
}

class_alias(
    \Doctrine\Persistence\Event\LoadClassMetadataEventArgs::class,
    __NAMESPACE__ . '\LoadClassMetadataEventArgs'
);

if (false) {
    /**
     * @deprecated 1.3 Use Doctrine\Persistence\Event\LoadClassMetadataEventArgs
     */
    class LoadClassMetadataEventArgs extends \Doctrine\Persistence\Event\LoadClassMetadataEventArgs
    {
    }
}
