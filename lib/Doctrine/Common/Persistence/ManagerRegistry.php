<?php

namespace Doctrine\Common\Persistence;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\ManagerRegistry')) {
    class_alias(
        \Doctrine\Persistence\ManagerRegistry::class,
        __NAMESPACE__ . '\ManagerRegistry'
    );
}

if (false) {
    interface ManagerRegistry extends \Doctrine\Persistence\ManagerRegistry
    {
    }
}
