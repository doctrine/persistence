<?php

namespace Doctrine\Common\Persistence\Mapping\Driver;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\AnnotationDriver')) {
    class_alias(
        \Doctrine\Persistence\Mapping\Driver\AnnotationDriver::class,
        __NAMESPACE__ . '\AnnotationDriver'
    );
}

if (false) {
    abstract class AnnotationDriver extends \Doctrine\Persistence\Mapping\Driver\AnnotationDriver
    {
    }
}
