<?php

namespace Doctrine\Common\Persistence;

use function class_alias;

class_alias(
    \Doctrine\Persistence\ObjectManagerDecorator::class,
    __NAMESPACE__ . '\ObjectManagerDecorator'
);

if (false) {
    abstract class ObjectManagerDecorator extends \Doctrine\Persistence\ObjectManagerDecorator
    {
    }
}
