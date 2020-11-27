<?php

namespace Doctrine\Common\Persistence;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\ObjectManager')) {
    class_alias(
        \Doctrine\Persistence\ObjectManager::class,
        __NAMESPACE__ . '\ObjectManager'
    );
}

if (false) {
    interface ObjectManager extends \Doctrine\Persistence\ObjectManager
    {
    }
}
