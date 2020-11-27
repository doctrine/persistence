<?php

namespace Doctrine\Common\Persistence\Mapping\Driver;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\MappingDriverChain')) {
    class_alias(
        \Doctrine\Persistence\Mapping\Driver\MappingDriverChain::class,
        __NAMESPACE__ . '\MappingDriverChain'
    );
}

if (false) {
    class MappingDriverChain extends \Doctrine\Persistence\Mapping\Driver\MappingDriverChain
    {
    }
}
