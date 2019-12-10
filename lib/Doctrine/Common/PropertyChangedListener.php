<?php

namespace Doctrine\Common;

use const E_USER_DEPRECATED;
use function class_alias;
use function interface_exists;
use function sprintf;
use function trigger_error;

if (! interface_exists(\Doctrine\Persistence\PropertyChangedListener::class, false)) {
    @trigger_error(sprintf(
        'The %s\PropertyChangedListener class is deprecated since doctrine/persistence 1.3 and will be removed in 2.0.'
        . ' Use \Doctrine\Persistence\PropertyChangedListener instead.',
        __NAMESPACE__
    ), E_USER_DEPRECATED);
}

class_alias(
    \Doctrine\Persistence\PropertyChangedListener::class,
    __NAMESPACE__ . '\PropertyChangedListener'
);

if (false) {
    /**
     * @deprecated 1.3 Use Doctrine\Persistence\PropertyChangedListener
     */
    interface PropertyChangedListener extends \Doctrine\Persistence\PropertyChangedListener
    {
    }
}
