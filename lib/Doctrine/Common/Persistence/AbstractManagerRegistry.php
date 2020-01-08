<?php

namespace Doctrine\Common\Persistence;

use function class_alias;

class_alias(
    \Doctrine\Persistence\AbstractManagerRegistry::class,
    __NAMESPACE__ . '\AbstractManagerRegistry'
);

if (false) {
    abstract class AbstractManagerRegistry extends \Doctrine\Persistence\AbstractManagerRegistry
    {
    }
}
