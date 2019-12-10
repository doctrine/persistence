<?php

namespace Doctrine\Common\Persistence\Mapping;

use const E_USER_DEPRECATED;
use function class_alias;
use function class_exists;
use function sprintf;
use function trigger_error;

if (! class_exists(\Doctrine\Persistence\Mapping\StaticReflectionService::class, false)) {
    @trigger_error(sprintf(
        'The %s\StaticReflectionService class is deprecated since doctrine/persistence 1.3 and will be removed in 2.0.'
        . ' Use \Doctrine\Persistence\Mapping\StaticReflectionService instead.',
        __NAMESPACE__
    ), E_USER_DEPRECATED);
}

class_alias(
    \Doctrine\Persistence\Mapping\StaticReflectionService::class,
    __NAMESPACE__ . '\StaticReflectionService'
);

if (false) {
    /**
     * @deprecated 1.3 Use Doctrine\Persistence\Mapping\StaticReflectionService
     */
    class StaticReflectionService extends \Doctrine\Persistence\Mapping\StaticReflectionService
    {
    }
}
