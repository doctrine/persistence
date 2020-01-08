<?php

namespace Doctrine\Common\Persistence\Mapping\Driver;

use function class_alias;

class_alias(
    \Doctrine\Persistence\Mapping\Driver\AnnotationDriver::class,
    __NAMESPACE__ . '\AnnotationDriver'
);

if (false) {
    abstract class AnnotationDriver extends \Doctrine\Persistence\Mapping\Driver\AnnotationDriver
    {
    }
}
