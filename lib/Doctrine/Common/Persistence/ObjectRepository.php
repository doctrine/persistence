<?php

namespace Doctrine\Common\Persistence;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\ObjectRepository')) {
    class_alias(
        \Doctrine\Persistence\ObjectRepository::class,
        __NAMESPACE__ . '\ObjectRepository'
    );
}

if (false) {
    interface ObjectRepository extends \Doctrine\Persistence\ObjectRepository
    {
    }
}
