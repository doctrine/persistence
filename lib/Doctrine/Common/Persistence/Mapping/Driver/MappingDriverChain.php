<?php

namespace Doctrine\Common\Persistence\Mapping\Driver;

use const E_USER_DEPRECATED;
use function class_alias;
use function class_exists;
use function sprintf;
use function trigger_error;

if (! class_exists(\Doctrine\Persistence\Mapping\Driver\MappingDriverChain::class, false)) {
    @trigger_error(sprintf(
        'The %s\MappingDriverChain class is deprecated since doctrine/persistence 1.3 and will be removed in 2.0.'
        . ' Use \Doctrine\Persistence\Mapping\Driver\MappingDriverChain instead.',
        __NAMESPACE__
    ), E_USER_DEPRECATED);
}

class_alias(
    \Doctrine\Persistence\Mapping\Driver\MappingDriverChain::class,
    __NAMESPACE__ . '\MappingDriverChain'
);

if (false) {
    /**
     * @deprecated 1.3 Use Doctrine\Persistence\Mapping\Driver\MappingDriverChain
     */
    class MappingDriverChain extends \Doctrine\Persistence\Mapping\Driver\MappingDriverChain
    {
    }
}
