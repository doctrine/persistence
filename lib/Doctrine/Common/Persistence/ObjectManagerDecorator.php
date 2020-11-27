<?php

namespace Doctrine\Common\Persistence;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\ObjectManagerDecorator')) {
    class_alias(
        \Doctrine\Persistence\ObjectManagerDecorator::class,
        __NAMESPACE__ . '\ObjectManagerDecorator'
    );
}

if (false) {
    abstract class ObjectManagerDecorator extends \Doctrine\Persistence\ObjectManagerDecorator
    {
    }
}
