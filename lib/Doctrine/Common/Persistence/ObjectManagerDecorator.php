<?php

namespace Doctrine\Common\Persistence;

use const E_USER_DEPRECATED;
use function class_alias;
use function class_exists;
use function sprintf;
use function trigger_error;

if (! class_exists(\Doctrine\Persistence\ObjectManagerDecorator::class, false)) {
    @trigger_error(sprintf(
        'The %s\ObjectManagerDecorator class is deprecated since doctrine/persistence 1.3 and will be removed in 2.0.'
        . ' Use \Doctrine\Persistence\ObjectManagerDecorator instead.',
        __NAMESPACE__
    ), E_USER_DEPRECATED);
}

class_alias(
    \Doctrine\Persistence\ObjectManagerDecorator::class,
    __NAMESPACE__ . '\ObjectManagerDecorator'
);

if (false) {
    /**
     * @deprecated 1.3 Use Doctrine\Persistence\ObjectManagerDecorator
     */
    abstract class ObjectManagerDecorator extends \Doctrine\Persistence\ObjectManagerDecorator
    {
    }
}
