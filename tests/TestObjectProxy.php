<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence;

use Doctrine\Persistence\Proxy;
use Override;

/** @implements Proxy<TestObject> */
class TestObjectProxy extends TestObject implements Proxy
{
    #[Override]
    public function __load(): void
    {
    }

    #[Override]
    public function __isInitialized(): bool
    {
        return true;
    }
}
