<?php

namespace Doctrine\Common\Persistence;

use const E_USER_DEPRECATED;
use function class_alias;
use function class_exists;
use function sprintf;
use function trigger_error;

if (! class_exists(\Doctrine\Persistence\AbstractManagerRegistry::class, false)) {
    @trigger_error(sprintf(
        'The %s\AbstractManagerRegistry class is deprecated since doctrine/persistence 1.3 and will be removed in 2.0.'
        . ' Use \Doctrine\Persistence\AbstractManagerRegistry instead.',
        __NAMESPACE__
    ), E_USER_DEPRECATED);
}

class_alias(
    \Doctrine\Persistence\AbstractManagerRegistry::class,
    __NAMESPACE__ . '\AbstractManagerRegistry'
);

if (false) {
    /**
     * @deprecated 1.3 Use Doctrine\Persistence\AbstractManagerRegistry
     */
    abstract class AbstractManagerRegistry extends \Doctrine\Persistence\AbstractManagerRegistry
    {
    }
}
