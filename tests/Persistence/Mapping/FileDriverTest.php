<?php

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
        $driver = new TestFileDriver([]);

        self::assertNull($driver->getGlobalBasename());

        $driver->setGlobalBasename('global');
        self::assertSame('global', $driver->getGlobalBasename());
    }

    public function testGetElementFromGlobalFile(): void
    {
        $driver = new TestFileDriver($this->newLocator());
        $driver->setGlobalBasename('global');

        $element = $driver->getElement(GlobalClass::class);

        self::assertSame(GlobalClass::class, $element->getName());
    }

    public function testGetElementFromFile(): void
    {
        $locator = $this->newLocator();
        $locator->expects($this->once())
                ->method('findMappingFile')
                ->with($this->equalTo(stdClass::class))
                ->will($this->returnValue(__DIR__ . '/_files/stdClass.yml'));

        $driver = new TestFileDriver($locator);

        self::assertSame(stdClass::class, $driver->getElement(stdClass::class)->getName());
    }

    public function testGetElementUpdatesClassCache(): void
    {
        $locator = $this->newLocator();

        // findMappingFile should only be called once
        $locator->expects($this->once())
            ->method('findMappingFile')
            ->with($this->equalTo(stdClass::class))
            ->will($this->returnValue(__DIR__ . '/_files/stdClass.yml'));

        $driver = new TestFileDriver($locator);

        // not cached
        self::assertSame(stdClass::class, $driver->getElement(stdClass::class)->getName());

        // cached call
        self::assertSame(stdClass::class, $driver->getElement(stdClass::class)->getName());
    }

    public function testGetAllClassNamesGlobalBasename(): void
    {
        $driver = new TestFileDriver($this->newLocator());
        $driver->setGlobalBasename('global');

        $classNames = $driver->getAllClassNames();

        self::assertSame([GlobalClass::class, AnotherGlobalClass::class], $classNames);
    }

    public function testGetAllClassNamesFromMappingFile(): void
    {
        $locator = $this->newLocator();
        $locator->expects($this->any())
                ->method('getAllClassNames')
                ->with($this->equalTo(null))
                ->will($this->returnValue([stdClass::class]));
        $driver = new TestFileDriver($locator);

        $classNames = $driver->getAllClassNames();

        self::assertSame([stdClass::class], $classNames);
    }

    public function testGetAllClassNamesBothSources(): void
    {
        $locator = $this->newLocator();
        $locator->expects($this->any())
                ->method('getAllClassNames')
                ->with($this->equalTo('global'))
                ->will($this->returnValue([stdClass::class]));
        $driver = new TestFileDriver($locator);
        $driver->setGlobalBasename('global');

        $classNames = $driver->getAllClassNames();

        self::assertSame([GlobalClass::class, AnotherGlobalClass::class, stdClass::class], $classNames);
    }

    public function testGetAllClassNamesBothSourcesNoDupes(): void
    {
        $locator = $this->newLocator();
        $locator->expects($this->once())
                ->method('getAllClassNames')
                ->with($this->equalTo('global'))
                ->willReturn([stdClass::class]);
        $driver = new TestFileDriver($locator);
        $driver->setGlobalBasename('global');

        $driver->getElement(stdClass::class);
        $classNames = $driver->getAllClassNames();

        self::assertSame([GlobalClass::class, AnotherGlobalClass::class, stdClass::class], $classNames);
    }

    public function testIsNotTransient(): void
    {
        $locator = $this->newLocator();
        $locator->expects($this->once())
                ->method('fileExists')
                ->with($this->equalTo(stdClass::class))
                ->will($this->returnValue(true));

        $driver = new TestFileDriver($locator);
        $driver->setGlobalBasename('global');

        self::assertFalse($driver->isTransient(stdClass::class));
        self::assertFalse($driver->isTransient(GlobalClass::class));
        self::assertFalse($driver->isTransient(AnotherGlobalClass::class));
    }

    public function testIsTransient(): void
    {
        $locator = $this->newLocator();
        $locator->expects($this->once())
                ->method('fileExists')
                ->with($this->equalTo(NotLoadedClass::class))
                ->will($this->returnValue(false));

        $driver = new TestFileDriver($locator);

        self::assertTrue($driver->isTransient(NotLoadedClass::class));
    }

    public function testNonLocatorFallback(): void
    {
        $driver = new TestFileDriver(__DIR__ . '/_files', '.yml');
        self::assertTrue($driver->isTransient(NotLoadedClass::class));
        self::assertFalse($driver->isTransient(stdClass::class));
    }

    /**
     * @return MockObject&FileLocator
     */
    private function newLocator(): MockObject
    {
        $locator = $this->createMock(FileLocator::class);
        $locator->expects($this->any())->method('getFileExtension')->will($this->returnValue('.yml'));
        $locator->expects($this->any())->method('getPaths')->will($this->returnValue([__DIR__ . '/_files']));

        return $locator;
    }
}

class TestFileDriver extends FileDriver
{
    /**
     * {@inheritDoc}
     */
    protected function loadMappingFile($file)
    {
        if ($file && strpos($file, 'global.yml') !== false) {
            return [
                GlobalClass::class => new TestClassMetadata(GlobalClass::class),
                AnotherGlobalClass::class => new TestClassMetadata(AnotherGlobalClass::class),
            ];
        }

        return [stdClass::class => new TestClassMetadata(stdClass::class)];
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata): void
    {
    }
}
