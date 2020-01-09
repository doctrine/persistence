<?php

namespace Doctrine\Common\Persistence;

use function class_alias;

class_alias(
    \Doctrine\Persistence\ConnectionRegistry::class,
    __NAMESPACE__ . '\ConnectionRegistry'
);

if (false) {
    interface ConnectionRegistry extends \Doctrine\Persistence\ConnectionRegistry
    {
    }
}
