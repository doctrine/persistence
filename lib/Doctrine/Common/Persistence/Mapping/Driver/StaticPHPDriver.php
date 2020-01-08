<?php

namespace Doctrine\Common\Persistence\Mapping\Driver;

use function class_alias;

class_alias(
    \Doctrine\Persistence\Mapping\Driver\StaticPHPDriver::class,
    __NAMESPACE__ . '\StaticPHPDriver'
);

if (false) {
    class StaticPHPDriver extends \Doctrine\Persistence\Mapping\Driver\StaticPHPDriver
    {
    }
}
