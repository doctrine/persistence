<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence;

use Doctrine\Common\Persistence\PersistentObject;

class OtherTestObject extends PersistentObject
{
    /** @var int */
    protected $id = 1;
}
