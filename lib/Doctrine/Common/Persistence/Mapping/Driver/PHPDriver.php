<?php

namespace Doctrine\Common\Persistence\Mapping\Driver;

use function class_alias;

class_alias(
    \Doctrine\Persistence\Mapping\Driver\PHPDriver::class,
    __NAMESPACE__ . '\PHPDriver'
);

if (false) {
    class PHPDriver extends \Doctrine\Persistence\Mapping\Driver\PHPDriver
    {
    }
}
