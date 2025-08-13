<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping\Driver;

use DirectoryIterator;
use Doctrine\Persistence\Mapping\Driver\FileClassLocator;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Tests\Persistence\Mapping\_files\colocated\Entity;
use Doctrine\Tests\Persistence\Mapping\_files\colocated\EntityFixture;
use Doctrine\Tests\Persistence\Mapping\_files\colocated\Foo;
use Doctrine\Tests\Persistence\Mapping\_files\colocated\TestClass;
use EmptyIterator;
use Phar;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

use function dirname;
use function ini_get;
use function sort;
use function unlink;

use const SORT_STRING;

final class FileClassLocatorTest extends DoctrineTestCase
{
    public function testGetClassNames(): void
    {
        $locator = new FileClassLocator([
            new SplFileInfo(__DIR__ . '/../_files/colocated/Entity.php'),
            new SplFileInfo(__DIR__ . '/../_files/colocated/EntityFixture.php'),
        ]);

        $classes = $locator->getClassNames();
        sort($classes, SORT_STRING);

        self::assertSame([
            Entity::class,
            EntityFixture::class,
        ], $classes);
    }

    public function testGetClassNamesWithEmptyIterator(): void
    {
        $locator = new FileClassLocator(new EmptyIterator());
        self::assertSame([], $locator->getClassNames());
    }

    public function testCreateFromDirectory(): void
    {
        $locator = FileClassLocator::createFromDirectories([__DIR__ . '/../_files/colocated']);

        $classes = $locator->getClassNames();
        sort($classes, SORT_STRING);

        self::assertSame([
            Entity::class,
            EntityFixture::class,
            TestClass::class,
        ], $classes);
    }

    public function testCreateFromDirectoryWithExtension(): void
    {
        $locator = FileClassLocator::createFromDirectories([__DIR__ . '/../_files/colocated'], [], '.mphp');

        $classes = $locator->getClassNames();
        sort($classes, SORT_STRING);

        self::assertSame([Foo::class], $classes);
    }

    public function testCreateFromDirectoryWithNonExistentDirectory(): void
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessage('File mapping drivers must have a valid directory path, however the given path [/non/existent/directory] seems to be incorrect!');

        FileClassLocator::createFromDirectories(['/non/existent/directory']);
    }

    public function testCreateFromEmptyDirectory(): void
    {
        $locator = FileClassLocator::createFromDirectories([__DIR__ . '/../_files/Bar']);

        self::assertSame([], $locator->getClassNames());
    }

    public function testCreateFromDirectoryIterator(): void
    {
        $locator = new FileClassLocator(new DirectoryIterator(__DIR__ . '/../_files/colocated'));

        $classes = $locator->getClassNames();
        sort($classes, SORT_STRING);

        self::assertSame([
            Entity::class,
            EntityFixture::class,
            Foo::class,
            TestClass::class,
        ], $classes);
    }

    public function testCreateFromSymfonyFinder(): void
    {
        $finder = Finder::create()
            ->in(__DIR__ . '/../_files/colocated')
            ->name('*.php')
            ->notName('Test*');

        $locator = new FileClassLocator($finder);

        $classes = $locator->getClassNames();
        sort($classes, SORT_STRING);

        self::assertSame([
            Entity::class,
            EntityFixture::class,
        ], $classes);
    }

    public function testWithPharFiles(): void
    {
        if (ini_get('phar.readonly') === '1') {
            self::markTestSkipped('creating archive disabled by the php.ini setting phar.readonly');
        }

        // Create a temporary Phar file with a PHP class
        $pharFile = dirname(__DIR__) . '/_files/colocated.phar';
        $phar     = new Phar($pharFile);
        $phar->startBuffering();
        $phar->addFromString('Entity.php', '<?php namespace Doctrine\Phar; class Entity {}');
        $phar->addFromString('EntityFixture.php', '<?php namespace Doctrine\Phar; class EntityFixture {}');
        // Excludes directory
        $phar->addFromString('Excluded/EntityExcluded.php', '<?php namespace Doctrine\Phar\Excluded; class EntityExcluded {}');
        // Excluded file extension
        $phar->addFromString('Foo.mphp', '<?php namespace Doctrine\Phar; class Foo {}');
        $phar->stopBuffering();

        $locator = FileClassLocator::createFromDirectories(['phar://' . $pharFile], ['phar://' . $pharFile . '/Excluded']);

        $classes = $locator->getClassNames();
        sort($classes, SORT_STRING);
        unlink($pharFile);

        // @phpstan-ignore staticMethod.impossibleType
        self::assertSame([
            'Doctrine\Phar\Entity',
            'Doctrine\Phar\EntityFixture',
        ], $classes);
    }
}
