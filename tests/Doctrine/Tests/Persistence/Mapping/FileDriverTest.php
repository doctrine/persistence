<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Persistence\Mapping\Driver\FileLocator;
use Doctrine\Tests\DoctrineTestCase;
use PHPUnit\Framework\MockObject\MockObject;

use function strpos;

class FileDriverTest extends DoctrineTestCase
{
    public function testGlobalBasename(): void
    {
        $driver = $this->createTestFileDriver([]);

        self::assertSame('', $driver->getGlobalBasename());

        $driver->setGlobalBasename('global');

        self::assertSame('global', $driver->getGlobalBasename());
    }

    public function testGetElementFromGlobalFile(): void
    {
        $driver = $this->createTestFileDriver($this->newLocator());

        $driver->setGlobalBasename('global');

        $element = $driver->getElement('stdGlobal');

        self::assertSame($driver->stdGlobal, $element);
    }

    public function testGetElementFromFile(): void
    {
        $locator = $this->newLocator();
        $locator->expects(self::once())
                ->method('findMappingFile')
                ->with(self::equalTo('stdClass'))
                ->will(self::returnValue(__DIR__ . '/_files/stdClass.yml'));

        $driver = $this->createTestFileDriver($locator);

        self::assertSame($driver->stdClass, $driver->getElement('stdClass'));
    }

    public function testGetElementUpdatesClassCache(): void
    {
        $locator = $this->newLocator();

        // findMappingFile should only be called once
        $locator->expects(self::once())
            ->method('findMappingFile')
            ->with(self::equalTo('stdClass'))
            ->will(self::returnValue(__DIR__ . '/_files/stdClass.yml'));

        $driver = $this->createTestFileDriver($locator);

        // not cached
        self::assertSame($driver->stdClass, $driver->getElement('stdClass'));

        // cached call
        self::assertSame($driver->stdClass, $driver->getElement('stdClass'));
    }

    public function testGetAllClassNamesGlobalBasename(): void
    {
        $locator = $this->newLocator();
        $locator->expects(self::any())->method('getAllClassNames')->with('global')->will(self::returnValue(['stdGlobal', 'stdGlobal2']));

        $driver = $this->createTestFileDriver($locator);
        $driver->setGlobalBasename('global');

        $classNames = $driver->getAllClassNames();

        self::assertSame(['stdGlobal', 'stdGlobal2'], $classNames);
    }

    public function testGetAllClassNamesFromMappingFile(): void
    {
        $locator = $this->newLocator();
        $locator->expects(self::any())
                ->method('getAllClassNames')
                ->with(self::equalTo(''))
                ->will(self::returnValue(['stdClass']));

        $driver = $this->createTestFileDriver($locator);

        $classNames = $driver->getAllClassNames();

        self::assertSame(['stdClass'], $classNames);
    }

    public function testGetAllClassNamesBothSources(): void
    {
        $locator = $this->newLocator();
        $locator->expects(self::any())
                ->method('getAllClassNames')
                ->with(self::equalTo('global'))
                ->will(self::returnValue(['stdClass']));

        $driver = $this->createTestFileDriver($locator);
        $driver->setGlobalBasename('global');

        $classNames = $driver->getAllClassNames();

        self::assertSame(['stdGlobal', 'stdGlobal2', 'stdClass'], $classNames);
    }

    public function testGetAllClassNamesBothSourcesNoDupes(): void
    {
        $locator = $this->newLocator();
        $locator->expects(self::once())
                ->method('getAllClassNames')
                ->with(self::equalTo('global'))
                ->willReturn(['stdClass']);

        $driver = $this->createTestFileDriver($locator);
        $driver->setGlobalBasename('global');

        $locator->expects(self::once())
                ->method('findMappingFile')
                ->with('stdClass')
                ->willReturn('');

        $driver->getElement('stdClass');
        $classNames = $driver->getAllClassNames();

        self::assertSame(['stdGlobal', 'stdGlobal2', 'stdClass'], $classNames);
    }

    public function testIsNotTransient(): void
    {
        $locator = $this->newLocator();
        $locator->expects(self::once())
                ->method('fileExists')
                ->with(self::equalTo('stdClass'))
                ->will(self::returnValue(true));

        $driver = $this->createTestFileDriver($locator);
        $driver->setGlobalBasename('global');

        self::assertFalse($driver->isTransient('stdClass'));
        self::assertFalse($driver->isTransient('stdGlobal'));
        self::assertFalse($driver->isTransient('stdGlobal2'));
    }

    public function testIsTransient(): void
    {
        $locator = $this->newLocator();
        $locator->expects(self::once())
                ->method('fileExists')
                ->with(self::equalTo('stdClass2'))
                ->will(self::returnValue(false));

        $driver = $this->createTestFileDriver($locator);

        self::assertTrue($driver->isTransient('stdClass2'));
    }

    public function testNonLocatorFallback(): void
    {
        $driver = $this->createTestFileDriver(__DIR__ . '/_files', '.yml');
        self::assertTrue($driver->isTransient('stdClass2'));
        self::assertFalse($driver->isTransient('stdClass'));
    }

    /**
     * @return FileLocator&MockObject
     */
    private function newLocator(): MockObject
    {
        $locator = $this->createMock(FileLocator::class);
        $locator->expects(self::any())->method('getFileExtension')->will(self::returnValue('.yml'));
        $locator->expects(self::any())->method('getPaths')->will(self::returnValue([__DIR__ . '/_files']));

        return $locator;
    }

    /**
     * @param string|array<int, string>|FileLocator $locator
     */
    private function createTestFileDriver($locator, ?string $fileExtension = null): TestFileDriver
    {
        $driver = new TestFileDriver($locator, $fileExtension);

        $driver->stdClass   = $this->createMock(ClassMetadata::class);
        $driver->stdGlobal  = $this->createMock(ClassMetadata::class);
        $driver->stdGlobal2 = $this->createMock(ClassMetadata::class);

        return $driver;
    }
}

class TestFileDriver extends FileDriver
{
    /** @var ClassMetadata */
    public $stdGlobal;

    /** @var ClassMetadata */
    public $stdGlobal2;

    /** @var ClassMetadata */
    public $stdClass;

    /**
     * {@inheritDoc}
     */
    protected function loadMappingFile(string $file): array
    {
        if (strpos($file, 'global.yml') !== false) {
            return [
                'stdGlobal' => $this->stdGlobal,
                'stdGlobal2' => $this->stdGlobal2,
            ];
        }

        return ['stdClass' => $this->stdClass];
    }

    public function loadMetadataForClass(string $className, ClassMetadata $metadata): void
    {
    }
}
