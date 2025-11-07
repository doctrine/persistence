<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping;

use Doctrine\Persistence\Proxy;

/** @deprecated Since 5.0: Native lazy objects don't use proxy classes anymore, this interface will be removed in Doctrine Persistence 6.0. */
interface ProxyClassNameResolver
{
    /**
     * @phpstan-param class-string<Proxy<T>>|class-string<T> $className
     *
     * @phpstan-return class-string<T>
     *
     * @template T of object
     */
    public function resolveClassName(string $className): string;
}
