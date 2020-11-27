<?php

namespace Doctrine\Common\Persistence\Mapping\Driver;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\SymfonyFileLocator')) {
    class_alias(
        \Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator::class,
        __NAMESPACE__ . '\SymfonyFileLocator'
    );
}

if (false) {
    class SymfonyFileLocator extends \Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator
    {
    }
}
