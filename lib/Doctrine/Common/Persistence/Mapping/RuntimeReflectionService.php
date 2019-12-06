<?php

namespace Doctrine\Common\Persistence\Mapping;

use const E_USER_DEPRECATED;
use function class_alias;
use function class_exists;
use function sprintf;
use function trigger_error;

if (! class_exists(\Doctrine\Persistence\Mapping\RuntimeReflectionService::class, false)) {
    @trigger_error(sprintf(
        'The %s\RuntimeReflectionService class is deprecated since doctrine/persistence 1.3 and will be removed in 2.0.'
        . ' Use \Doctrine\Persistence\Mapping\RuntimeReflectionService instead.',
        __NAMESPACE__
    ), E_USER_DEPRECATED);
}

class_alias(
    \Doctrine\Persistence\Mapping\RuntimeReflectionService::class,
    __NAMESPACE__ . '\RuntimeReflectionService'
);

if (false) {
    /**
     * @deprecated 1.3 Use Doctrine\Persistence\Mapping\RuntimeReflectionService
     */
    class RuntimeReflectionService extends \Doctrine\Persistence\Mapping\RuntimeReflectionService
    {
    }
}
