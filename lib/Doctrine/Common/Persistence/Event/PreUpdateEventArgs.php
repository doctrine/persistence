<?php

namespace Doctrine\Common\Persistence\Event;

use function class_alias;

class_alias(
    \Doctrine\Persistence\Event\PreUpdateEventArgs::class,
    __NAMESPACE__ . '\PreUpdateEventArgs'
);

if (false) {
    class PreUpdateEventArgs extends \Doctrine\Persistence\Event\PreUpdateEventArgs
    {
    }
}
