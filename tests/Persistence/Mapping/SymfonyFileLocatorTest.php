<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Tests\DoctrineTestCase;
use InvalidArgumentException;

use function realpath;
use function sort;

use const DIRECTORY_SEPARATOR;

class SymfonyFileLocatorTest extends DoctrineTestCase
{
    public function testGetPaths(): void
    {
        $path   = __DIR__ . '/_files';
        $prefix = 'Foo';

        $locator = new SymfonyFileLocator([$path => $prefix]);
        self::assertSame([$path], $locator->getPaths());

        $locator = new SymfonyFileLocator([$path => $prefix]);
        self::assertSame([$path], $locator->getPaths());
    }

    public function testGetPrefixes(): void
    {
        $path   = __DIR__ . '/_files';
        $prefix = 'Foo';

        $locator = new SymfonyFileLocator([$path => $prefix]);
        self::assertSame([$path => $prefix], $locator->getNamespacePrefixes());
    }

    public function testGetFileExtension(): void
    {
        $locator = new SymfonyFileLocator([], '.yml');
        self::assertSame('.yml', $locator->getFileExtension());
        $locator->setFileExtension('.xml');
        self::assertSame('.xml', $locator->getFileExtension());
    }

    public function testFileExists(): void
    {
        $path   = __DIR__ . '/_files';
        $prefix = 'Foo';

        $locator = new SymfonyFileLocator([$path => $prefix], '.yml');

        self::assertTrue($locator->fileExists('Foo\stdClass'));
        self::assertTrue($locator->fileExists('Foo\global'));
        self::assertFalse($locator->fileExists('Foo\stdClass2'));
        self::assertFalse($locator->fileExists('Foo\global2'));
    }

    public function testGetAllClassNames(): void
    {
        $path   = __DIR__ . '/_files';
        $prefix = 'Foo';

        $locator       = new SymfonyFileLocator([$path => $prefix], '.yml');
        $allClasses    = $locator->getAllClassNames('');
        $globalClasses = $locator->getAllClassNames('global');

        $expectedAllClasses    = ['Foo\\Bar\\subDirClass', 'Foo\\global', 'Foo\\stdClass'];
        $expectedGlobalClasses = ['Foo\\Bar\\subDirClass', 'Foo\\stdClass'];

        sort($allClasses);
        sort($globalClasses);
        sort($expectedAllClasses);
        sort($expectedGlobalClasses);

        self::assertSame($expectedAllClasses, $allClasses);
        self::assertSame($expectedGlobalClasses, $globalClasses);
    }

    public function testInvalidCustomNamespaceSeparator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Namespace separator should not be empty');
        $path   = __DIR__ . '/_files';
        $prefix = 'Foo';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Namespace separator should not be empty');

        new SymfonyFileLocator([$path => $prefix], '.yml', '');
    }

    /** @return array<string, array{string, string}> */
    public static function customNamespaceSeparatorProvider(): array
    {
        return [
            'directory separator' => [DIRECTORY_SEPARATOR, '/_custom_ns/dir'],
            'default dot separator' => ['.', '/_custom_ns/dot'],
        ];
    }

    /**
     * @param string $separator Directory separator to test against
     * @param string $dir       Path to load mapping data from
     *
     * @throws MappingException
     *
     * @dataProvider customNamespaceSeparatorProvider
     */
    public function testGetClassNamesWithCustomNsSeparator(string $separator, string $dir): void
    {
        $path   = __DIR__ . $dir;
        $prefix = 'Foo';

        $locator = new SymfonyFileLocator([$path => $prefix], '.yml', $separator);
        $classes = $locator->getAllClassNames('');
        sort($classes);

        self::assertSame(['Foo\\stdClass', 'Foo\\sub\\subClass', 'Foo\\sub\\subsub\\subSubClass'], $classes);
    }

    /** @return array<array{string, string, array<string, string>}> */
    public static function customNamespaceLookupQueryProvider(): array
    {
        return [
            'directory separator'  => [
                DIRECTORY_SEPARATOR,
                '/_custom_ns/dir',
                [
                    'stdClass.yml'               => 'Foo\\stdClass',
                    'sub/subClass.yml'           => 'Foo\\sub\\subClass',
                    'sub/subsub/subSubClass.yml' => 'Foo\\sub\\subsub\\subSubClass',
                ],
            ],
            'default dot separator' => [
                '.',
                '/_custom_ns/dot',
                [
                    'stdClass.yml'               => 'Foo\\stdClass',
                    'sub.subClass.yml'           => 'Foo\\sub\\subClass',
                    'sub.subsub.subSubClass.yml' => 'Foo\\sub\\subsub\\subSubClass',
                ],
            ],
        ];
    }

    /**
     * @param string   $separator Directory separator to test against
     * @param string   $dir       Path to load mapping data from
     * @param string[] $files     Files to lookup classnames
     *
     * @throws MappingException
     *
     * @dataProvider customNamespaceLookupQueryProvider
     */
    public function testFindMappingFileWithCustomNsSeparator(string $separator, string $dir, array $files): void
    {
        $path   = __DIR__ . $dir;
        $prefix = 'Foo';

        $locator = new SymfonyFileLocator([$path => $prefix], '.yml', $separator);

        foreach ($files as $filePath => $className) {
            self::assertSame(realpath($path . '/' . $filePath), realpath($locator->findMappingFile($className)));
        }
    }

    public function testFindMappingFile(): void
    {
        $path   = __DIR__ . '/_files';
        $prefix = 'Foo';

        $locator = new SymfonyFileLocator([$path => $prefix], '.yml');

        self::assertSame(__DIR__ . '/_files/stdClass.yml', $locator->findMappingFile('Foo\\stdClass'));
    }

    public function testFindMappingFileNotFound(): void
    {
        $path   = __DIR__ . '/_files';
        $prefix = 'Foo';

        $locator = new SymfonyFileLocator([$path => $prefix], '.yml');

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("No mapping file found named 'stdClass2.yml' for class 'Foo\stdClass2'.");
        $locator->findMappingFile('Foo\\stdClass2');
    }

    public function testFindMappingFileLeastSpecificNamespaceFirst(): void
    {
        // Low -> High
        $prefixes                             = [];
        $prefixes[__DIR__ . '/_match_ns']     = 'Foo';
        $prefixes[__DIR__ . '/_match_ns/Bar'] = 'Foo\\Bar';

        $locator = new SymfonyFileLocator($prefixes, '.yml');

        self::assertSame(
            __DIR__ . '/_match_ns/Bar/barEntity.yml',
            $locator->findMappingFile("Foo\\Bar\\barEntity")
        );
    }

    public function testFindMappingFileMostSpecificNamespaceFirst(): void
    {
        $prefixes                             = [];
        $prefixes[__DIR__ . '/_match_ns/Bar'] = 'Foo\\Bar';
        $prefixes[__DIR__ . '/_match_ns']     = 'Foo';

        $locator = new SymfonyFileLocator($prefixes, '.yml');

        self::assertSame(
            __DIR__ . '/_match_ns/Bar/barEntity.yml',
            $locator->findMappingFile("Foo\\Bar\\barEntity")
        );
    }
}
