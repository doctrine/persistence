<?php

namespace Doctrine\Common\Persistence;

use function class_alias;

class_alias(
    \Doctrine\Persistence\ManagerRegistry::class,
    __NAMESPACE__ . '\ManagerRegistry'
);

if (false) {
    interface ManagerRegistry extends \Doctrine\Persistence\ManagerRegistry
    {
    }
}
