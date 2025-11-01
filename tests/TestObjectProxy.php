<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence;

use Doctrine\Persistence\Proxy;

/** @implements Proxy<TestObject> */
class TestObjectProxy extends TestObject implements Proxy
{
    public function __load(): void
    {
    }

    public function __isInitialized(): bool
    {
        return true;
    }
}
