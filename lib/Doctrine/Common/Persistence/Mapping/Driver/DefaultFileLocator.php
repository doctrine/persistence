<?php

namespace Doctrine\Common\Persistence\Mapping\Driver;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\DefaultFileLocator')) {
    class_alias(
        \Doctrine\Persistence\Mapping\Driver\DefaultFileLocator::class,
        __NAMESPACE__ . '\DefaultFileLocator'
    );
}

if (false) {
    class DefaultFileLocator extends \Doctrine\Persistence\Mapping\Driver\DefaultFileLocator
    {
    }
}
