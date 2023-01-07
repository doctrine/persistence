<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Proxy

{

    use Doctrine\Persistence\Proxy\ProxyResolver;
    use Doctrine\Tests\DoctrineTestCase;

    class ProxyResolverTest extends DoctrineTestCase
    {
        /** @psalm-return list<array{class-string, class-string}> */
        public static function dataGetClass()
        {
            return [
                [\stdClass::class, \stdClass::class],
                [ProxyResolver::class, ProxyResolver::class],
                ['MyProject\Proxies\__CG__\stdClass', \stdClass::class],
                ['MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass', \stdClass::class],
                ['MyProject\Proxies\__CG__\Doctrine\Tests\Persistence\Proxy\ChildObject', ChildObject::class],
            ];
        }

        /**
         * @param class-string $className
         * @param class-string $expectedClassName
         *
         * @dataProvider dataGetClass
         */
        public function testGetRealClass(string $className, string $expectedClassName): void
        {
            self::assertEquals($expectedClassName, ProxyResolver::getRealClass($className));
        }

        /**
         * @param class-string $className
         * @param class-string $expectedClassName
         *
         * @dataProvider dataGetClass
         */
        public function testGetClass(string $className, string $expectedClassName): void
        {
            $object = new $className();
            self::assertEquals($expectedClassName, ProxyResolver::getClass($object));
        }

        public function testGetParentClass(): void
        {
            /** @psalm-var class-string $class */
            $class       = 'MyProject\Proxies\__CG__\Doctrine\Tests\Persistence\Proxy\ChildObject';
            $parentClass = ProxyResolver::getParentClass($class);
            self::assertEquals('stdClass', $parentClass);
        }

        public function testGenerateProxyClassName(): void
        {
            self::assertEquals('Proxies\__CG__\stdClass', ProxyResolver::generateProxyClassName(\stdClass::class, 'Proxies'));
        }

        /**
         * @param class-string $className
         * @param class-string $expectedClassName
         *
         * @dataProvider dataGetClass
         */
        public function testNewReflectionClass(string $className, string $expectedClassName): void
        {
            $reflClass = ProxyResolver::newReflectionClass($className);
            self::assertEquals($expectedClassName, $reflClass->getName());
        }

        /**
         * @param class-string $className
         * @param class-string $expectedClassName
         *
         * @dataProvider dataGetClass
         */
        public function testNewReflectionObject(string $className, string $expectedClassName): void
        {
            $object    = new $className();
            $reflClass = ProxyResolver::newReflectionObject($object);
            self::assertEquals($expectedClassName, $reflClass->getName());
        }
    }

    class ChildObject extends \stdClass
    {
    }
}

namespace MyProject\Proxies\__CG__

{
    class stdClass extends \stdClass // phpcs:ignore
    {
    }
}

namespace MyProject\Proxies\__CG__\Doctrine\Tests\Persistence\Proxy

{
    class ChildObject extends \Doctrine\Tests\Persistence\Proxy\ChildObject
    {
    }
}

namespace MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__

{
    class stdClass extends \MyProject\Proxies\__CG__\stdClass // phpcs:ignore
    {
    }
}

namespace MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\Doctrine\Tests\Persistence\Proxy

{
    class ChildObject extends \MyProject\Proxies\__CG__\Doctrine\Tests\Persistence\Proxy\ChildObject
    {
    }
}
