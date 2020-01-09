<?php

namespace Doctrine\Common\Persistence;

use function class_alias;

class_alias(
    \Doctrine\Persistence\ObjectManagerAware::class,
    __NAMESPACE__ . '\ObjectManagerAware'
);

if (false) {
    interface ObjectManagerAware extends \Doctrine\Persistence\ObjectManagerAware
    {
    }
}
