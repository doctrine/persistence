<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Persistence\Mapping\Driver\DefaultFileLocator;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Tests\DoctrineTestCase;
use const DIRECTORY_SEPARATOR;
use function sort;

class DefaultFileLocatorTest extends DoctrineTestCase
{
    public function testGetPaths()
    {
        $path = __DIR__ . '/_files';

        $locator = new DefaultFileLocator([$path]);
        self::assertSame([$path], $locator->getPaths());

        $locator = new DefaultFileLocator($path);
        self::assertSame([$path], $locator->getPaths());
    }

    public function testGetFileExtension()
    {
        $locator = new DefaultFileLocator([], '.yml');
        self::assertSame('.yml', $locator->getFileExtension());
        $locator->setFileExtension('.xml');
        self::assertSame('.xml', $locator->getFileExtension());
    }

    public function testUniquePaths()
    {
        $path = __DIR__ . '/_files';

        $locator = new DefaultFileLocator([$path, $path]);
        self::assertSame([$path], $locator->getPaths());
    }

    public function testFindMappingFile()
    {
        $path = __DIR__ . '/_files';

        $locator = new DefaultFileLocator([$path], '.yml');

        self::assertSame(__DIR__ . '/_files' . DIRECTORY_SEPARATOR . 'stdClass.yml', $locator->findMappingFile('stdClass'));
    }

    public function testFindMappingFileNotFound()
    {
        $path = __DIR__ . '/_files';

        $locator = new DefaultFileLocator([$path], '.yml');

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("No mapping file found named 'stdClass2.yml' for class 'stdClass2'");
        $locator->findMappingFile('stdClass2');
    }

    public function testGetAllClassNames()
    {
        $path = __DIR__ . '/_files';

        $locator       = new DefaultFileLocator([$path], '.yml');
        $allClasses    = $locator->getAllClassNames(null);
        $globalClasses = $locator->getAllClassNames('global');

        $expectedAllClasses    = ['global', 'stdClass', 'subDirClass'];
        $expectedGlobalClasses = ['subDirClass', 'stdClass'];

        sort($allClasses);
        sort($globalClasses);
        sort($expectedAllClasses);
        sort($expectedGlobalClasses);

        self::assertSame($expectedAllClasses, $allClasses);
        self::assertSame($expectedGlobalClasses, $globalClasses);
    }

    public function testGetAllClassNamesNonMatchingFileExtension()
    {
        $path = __DIR__ . '/_files';

        $locator = new DefaultFileLocator([$path], '.xml');
        self::assertSame([], $locator->getAllClassNames('global'));
    }

    public function testFileExists()
    {
        $path = __DIR__ . '/_files';

        $locator = new DefaultFileLocator([$path], '.yml');

        self::assertTrue($locator->fileExists('stdClass'));
        self::assertFalse($locator->fileExists('stdClass2'));
        self::assertTrue($locator->fileExists('global'));
        self::assertFalse($locator->fileExists('global2'));
    }
}
