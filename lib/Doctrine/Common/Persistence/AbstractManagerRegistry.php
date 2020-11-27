<?php

namespace Doctrine\Common\Persistence;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\AbstractManagerRegistry')) {
    class_alias(
        \Doctrine\Persistence\AbstractManagerRegistry::class,
        __NAMESPACE__ . '\AbstractManagerRegistry'
    );
}

if (false) {
    abstract class AbstractManagerRegistry extends \Doctrine\Persistence\AbstractManagerRegistry
    {
    }
}
