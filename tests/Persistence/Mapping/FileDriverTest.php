<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Persistence\Mapping\Driver\FileLocator;
use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Tests\Persistence\Mapping\Fixtures\AnotherGlobalClass;
use Doctrine\Tests\Persistence\Mapping\Fixtures\GlobalClass;
use Doctrine\Tests\Persistence\Mapping\Fixtures\NotLoadedClass;
use Doctrine\Tests\Persistence\Mapping\Fixtures\TestClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

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

        $element = $driver->getElement(GlobalClass::class);

        self::assertSame(GlobalClass::class, $element->getName());
    }

    public function testGetElementFromFile(): void
    {
        $locator = $this->newLocator();
        $locator->expects(self::once())
                ->method('findMappingFile')
                ->with(self::equalTo(stdClass::class))
                ->will(self::returnValue(__DIR__ . '/_files/stdClass.yml'));

        $driver = $this->createTestFileDriver($locator);

        self::assertSame(stdClass::class, $driver->getElement(stdClass::class)->getName());
    }

    public function testGetElementUpdatesClassCache(): void
    {
        $locator = $this->newLocator();

        // findMappingFile should only be called once
        $locator->expects(self::once())
            ->method('findMappingFile')
            ->with(self::equalTo(stdClass::class))
            ->will(self::returnValue(__DIR__ . '/_files/stdClass.yml'));

        $driver = $this->createTestFileDriver($locator);

        // not cached
        self::assertSame(stdClass::class, $driver->getElement(stdClass::class)->getName());

        // cached call
        self::assertSame(stdClass::class, $driver->getElement(stdClass::class)->getName());
    }

    public function testGetAllClassNamesGlobalBasename(): void
    {
        $locator = $this->newLocator();
        $locator->expects(self::any())->method('getAllClassNames')->with('global')->will(self::returnValue([
            GlobalClass::class,
            AnotherGlobalClass::class,
        ]));

        $driver = $this->createTestFileDriver($locator);
        $driver->setGlobalBasename('global');

        $classNames = $driver->getAllClassNames();

        self::assertSame([GlobalClass::class, AnotherGlobalClass::class], $classNames);
    }

    public function testGetAllClassNamesFromMappingFile(): void
    {
        $locator = $this->newLocator();
        $locator->expects(self::any())
                ->method('getAllClassNames')
                ->with(self::equalTo(null))
                ->will(self::returnValue([stdClass::class]));
        $driver = new TestFileDriver($locator);

        $classNames = $driver->getAllClassNames();

        self::assertSame([stdClass::class], $classNames);
    }

    public function testGetAllClassNamesBothSources(): void
    {
        $locator = $this->newLocator();
        $locator->expects(self::any())
                ->method('getAllClassNames')
                ->with(self::equalTo('global'))
                ->will(self::returnValue([stdClass::class]));
        $driver = new TestFileDriver($locator);
        $driver->setGlobalBasename('global');

        $classNames = $driver->getAllClassNames();

        self::assertSame([GlobalClass::class, AnotherGlobalClass::class, stdClass::class], $classNames);
    }

    public function testGetAllClassNamesBothSourcesNoDupes(): void
    {
        $locator = $this->newLocator();
        $locator->expects(self::once())
                ->method('getAllClassNames')
                ->with(self::equalTo('global'))
                ->willReturn([stdClass::class]);
        $locator->expects(self::once())
                ->method('findMappingFile')
                ->with(self::equalTo(stdClass::class))
                ->will(self::returnValue(__DIR__ . '/_files/stdClass.yml'));
        $driver = new TestFileDriver($locator);
        $driver->setGlobalBasename('global');

        $driver->getElement(stdClass::class);
        $classNames = $driver->getAllClassNames();

        self::assertSame([GlobalClass::class, AnotherGlobalClass::class, stdClass::class], $classNames);
    }

    public function testIsNotTransient(): void
    {
        $locator = $this->newLocator();
        $locator->expects(self::once())
                ->method('fileExists')
                ->with(self::equalTo(stdClass::class))
                ->will(self::returnValue(true));

        $driver = $this->createTestFileDriver($locator);
        $driver->setGlobalBasename('global');

        self::assertFalse($driver->isTransient(stdClass::class));
        self::assertFalse($driver->isTransient(GlobalClass::class));
        self::assertFalse($driver->isTransient(AnotherGlobalClass::class));
    }

    public function testIsTransient(): void
    {
        $locator = $this->newLocator();
        $locator->expects(self::once())
                ->method('fileExists')
                ->with(self::equalTo(NotLoadedClass::class))
                ->will(self::returnValue(false));

        $driver = $this->createTestFileDriver($locator);

        self::assertTrue($driver->isTransient(NotLoadedClass::class));
    }

    public function testNonLocatorFallback(): void
    {
        $driver = new TestFileDriver(__DIR__ . '/_files', '.yml');
        self::assertTrue($driver->isTransient(NotLoadedClass::class));
        self::assertFalse($driver->isTransient(stdClass::class));
    }

    /** @return FileLocator&MockObject */
    private function newLocator(): MockObject
    {
        $locator = $this->createMock(FileLocator::class);
        $locator->expects(self::any())->method('getFileExtension')->will(self::returnValue('.yml'));
        $locator->expects(self::any())->method('getPaths')->will(self::returnValue([__DIR__ . '/_files']));

        return $locator;
    }

    /** @param string|array<int, string>|FileLocator $locator */
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
    /** @var ClassMetadata<object> */
    public $stdGlobal;

    /** @var ClassMetadata<object> */
    public $stdGlobal2;

    /** @var ClassMetadata<object> */
    public $stdClass;

    /**
     * {@inheritDoc}
     */
    protected function loadMappingFile(string $file): array
    {
        if (strpos($file, 'global.yml') !== false) {
            return [
                GlobalClass::class => new TestClassMetadata(GlobalClass::class),
                AnotherGlobalClass::class => new TestClassMetadata(AnotherGlobalClass::class),
            ];
        }

        return [stdClass::class => new TestClassMetadata(stdClass::class)];
    }

    /** @param ClassMetadata<object> $metadata */
    public function loadMetadataForClass(string $className, ClassMetadata $metadata): void
    {
    }
}
