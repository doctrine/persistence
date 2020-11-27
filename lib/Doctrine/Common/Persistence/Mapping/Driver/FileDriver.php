<?php

namespace Doctrine\Common\Persistence\Mapping\Driver;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\FileDriver')) {
    class_alias(
        \Doctrine\Persistence\Mapping\Driver\FileDriver::class,
        __NAMESPACE__ . '\FileDriver'
    );
}

if (false) {
    abstract class FileDriver extends \Doctrine\Persistence\Mapping\Driver\FileDriver
    {
    }
}
