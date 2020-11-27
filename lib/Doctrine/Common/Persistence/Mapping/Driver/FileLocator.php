<?php

namespace Doctrine\Common\Persistence\Mapping\Driver;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\FileLocator')) {
    class_alias(
        \Doctrine\Persistence\Mapping\Driver\FileLocator::class,
        __NAMESPACE__ . '\FileLocator'
    );
}

if (false) {
    interface FileLocator extends \Doctrine\Persistence\Mapping\Driver\FileLocator
    {
    }
}
