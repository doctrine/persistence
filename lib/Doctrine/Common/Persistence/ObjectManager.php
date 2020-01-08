<?php

namespace Doctrine\Common\Persistence;

use function class_alias;

class_alias(
    \Doctrine\Persistence\ObjectManager::class,
    __NAMESPACE__ . '\ObjectManager'
);

if (false) {
    interface ObjectManager extends \Doctrine\Persistence\ObjectManager
    {
    }
}
