<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Tests\DoctrineTestCase;
use const DIRECTORY_SEPARATOR;
use function realpath;
use function sort;

class SymfonyFileLocatorTest extends DoctrineTestCase
{
    public function testGetPaths()
    {
        $path   = __DIR__ . '/_files';
        $prefix = 'Foo';

        $locator = new SymfonyFileLocator([$path => $prefix]);
        self::assertSame([$path], $locator->getPaths());

        $locator = new SymfonyFileLocator([$path => $prefix]);
        self::assertSame([$path], $locator->getPaths());
    }

    public function testGetPrefixes()
    {
        $path   = __DIR__ . '/_files';
        $prefix = 'Foo';

        $locator = new SymfonyFileLocator([$path => $prefix]);
        self::assertSame([$path => $prefix], $locator->getNamespacePrefixes());
    }

    public function testGetFileExtension()
    {
        $locator = new SymfonyFileLocator([], '.yml');
        self::assertSame('.yml', $locator->getFileExtension());
        $locator->setFileExtension('.xml');
        self::assertSame('.xml', $locator->getFileExtension());
    }

    public function testFileExists()
    {
        $path   = __DIR__ . '/_files';
        $prefix = 'Foo';

        $locator = new SymfonyFileLocator([$path => $prefix], '.yml');

        self::assertTrue($locator->fileExists('Foo\stdClass'));
        self::assertTrue($locator->fileExists('Foo\global'));
        self::assertFalse($locator->fileExists('Foo\stdClass2'));
        self::assertFalse($locator->fileExists('Foo\global2'));
    }

    public function testGetAllClassNames()
    {
        $path   = __DIR__ . '/_files';
        $prefix = 'Foo';

        $locator       = new SymfonyFileLocator([$path => $prefix], '.yml');
        $allClasses    = $locator->getAllClassNames(null);
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Namespace separator should not be empty
     */
    public function testInvalidCustomNamespaceSeparator()
    {
        $path   = __DIR__ . '/_files';
        $prefix = 'Foo';

        new SymfonyFileLocator([$path => $prefix], '.yml', null);
    }

    public function customNamespaceSeparatorProvider()
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
    public function testGetClassNamesWithCustomNsSeparator($separator, $dir)
    {
        $path   = __DIR__ . $dir;
        $prefix = 'Foo';

        $locator = new SymfonyFileLocator([$path => $prefix], '.yml', $separator);
        $classes = $locator->getAllClassNames(null);
        sort($classes);

        self::assertSame(['Foo\\stdClass', 'Foo\\sub\\subClass', 'Foo\\sub\\subsub\\subSubClass'], $classes);
    }

    public function customNamespaceLookupQueryProvider()
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
    public function testFindMappingFileWithCustomNsSeparator($separator, $dir, $files)
    {
        $path   = __DIR__ . $dir;
        $prefix = 'Foo';

        $locator = new SymfonyFileLocator([$path => $prefix], '.yml', $separator);

        foreach ($files as $filePath => $className) {
            self::assertSame(realpath($path . '/' . $filePath), realpath($locator->findMappingFile($className)));
        }
    }

    public function testFindMappingFile()
    {
        $path   = __DIR__ . '/_files';
        $prefix = 'Foo';

        $locator = new SymfonyFileLocator([$path => $prefix], '.yml');

        self::assertSame(__DIR__ . '/_files/stdClass.yml', $locator->findMappingFile('Foo\\stdClass'));
    }

    public function testFindMappingFileNotFound()
    {
        $path   = __DIR__ . '/_files';
        $prefix = 'Foo';

        $locator = new SymfonyFileLocator([$path => $prefix], '.yml');

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("No mapping file found named 'stdClass2.yml' for class 'Foo\stdClass2'.");
        $locator->findMappingFile('Foo\\stdClass2');
    }

    public function testFindMappingFileLeastSpecificNamespaceFirst()
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

    public function testFindMappingFileMostSpecificNamespaceFirst()
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
