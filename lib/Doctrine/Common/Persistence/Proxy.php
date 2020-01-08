<?php

namespace Doctrine\Common\Persistence;

use function class_alias;

class_alias(
    \Doctrine\Persistence\Proxy::class,
    __NAMESPACE__ . '\Proxy'
);

if (false) {
    interface Proxy extends \Doctrine\Persistence\Proxy
    {
    }
}
