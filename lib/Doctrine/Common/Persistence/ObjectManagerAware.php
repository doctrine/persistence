<?php

namespace Doctrine\Common\Persistence;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\ObjectManagerAware')) {
    class_alias(
        \Doctrine\Persistence\ObjectManagerAware::class,
        __NAMESPACE__ . '\ObjectManagerAware'
    );
}

if (false) {
    interface ObjectManagerAware extends \Doctrine\Persistence\ObjectManagerAware
    {
    }
}
