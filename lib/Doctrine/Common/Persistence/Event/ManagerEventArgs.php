<?php

namespace Doctrine\Common\Persistence\Event;

use function class_alias;

class_alias(
    \Doctrine\Persistence\Event\ManagerEventArgs::class,
    __NAMESPACE__ . '\ManagerEventArgs'
);

if (false) {
    class ManagerEventArgs extends \Doctrine\Persistence\Event\ManagerEventArgs
    {
    }
}
