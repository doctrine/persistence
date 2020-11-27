<?php

namespace Doctrine\Common\Persistence;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\ConnectionRegistry')) {
    class_alias(
        \Doctrine\Persistence\ConnectionRegistry::class,
        __NAMESPACE__ . '\ConnectionRegistry'
    );
}

if (false) {
    interface ConnectionRegistry extends \Doctrine\Persistence\ConnectionRegistry
    {
    }
}
