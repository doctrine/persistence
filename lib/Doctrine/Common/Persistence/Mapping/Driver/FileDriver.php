<?php

namespace Doctrine\Common\Persistence\Mapping\Driver;

use function class_alias;

class_alias(
    \Doctrine\Persistence\Mapping\Driver\FileDriver::class,
    __NAMESPACE__ . '\FileDriver'
);

if (false) {
    abstract class FileDriver extends \Doctrine\Persistence\Mapping\Driver\FileDriver
    {
    }
}
