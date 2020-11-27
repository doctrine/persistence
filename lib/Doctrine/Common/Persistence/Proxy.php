<?php

namespace Doctrine\Common\Persistence;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\Proxy')) {
    class_alias(
        \Doctrine\Persistence\Proxy::class,
        __NAMESPACE__ . '\Proxy'
    );
}

if (false) {
    interface Proxy extends \Doctrine\Persistence\Proxy
    {
    }
}
