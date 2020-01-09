<?php

namespace Doctrine\Common\Persistence\Event;

use function class_alias;

class_alias(
    \Doctrine\Persistence\Event\OnClearEventArgs::class,
    __NAMESPACE__ . '\OnClearEventArgs'
);

if (false) {
    class OnClearEventArgs extends \Doctrine\Persistence\Event\OnClearEventArgs
    {
    }
}
