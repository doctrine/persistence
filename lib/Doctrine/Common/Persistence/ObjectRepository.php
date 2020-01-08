<?php

namespace Doctrine\Common\Persistence;

use function class_alias;

class_alias(
    \Doctrine\Persistence\ObjectRepository::class,
    __NAMESPACE__ . '\ObjectRepository'
);

if (false) {
    interface ObjectRepository extends \Doctrine\Persistence\ObjectRepository
    {
    }
}
