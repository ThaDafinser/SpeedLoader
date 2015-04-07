<?php
namespace SpeedLoaderTest;

use PHPUnit_Framework_TestCase;
use SpeedLoader\BuildCache;
use SpeedLoader\BuildClass;

/**
 * @covers \SpeedLoader\BuildCache
 */
class BuildCacheTest extends PHPUnit_Framework_TestCase
{
    public function testSetGetNewLine()
    {
        $buildCache = new BuildCache();

        $this->assertEquals("\n", $buildCache->getNewLine());

        $buildCache->setNewLine("\r\n");
        $this->assertEquals("\r\n", $buildCache->getNewLine());
    }

    public function testSetGetCompressionLevel()
    {
        $buildCache = new BuildCache();

        $this->assertEquals(BuildClass::COMPRESS_NONE, $buildCache->getCompressionLevel());

        $buildCache->setCompressionLevel(BuildClass::COMPRESS_HIGH);
        $this->assertEquals(BuildClass::COMPRESS_HIGH, $buildCache->getCompressionLevel());
    }

    public function testSetGetClasses()
    {
        $buildCache = new BuildCache();

        $this->assertInternalType('array', $buildCache->getClasses());

        $classes = [
            'stdClass',
            'IntlSomeThing',
        ];
        $buildCache->setClasses($classes);
        $this->assertEquals($classes, $buildCache->getClasses());
    }

    public function testThrowExceptionIfGetCachedStringISCalledWithoutAClass()
    {
        $buildCache = new BuildCache();

        $this->setExpectedException('Exception');

        $buildCache->getCachedString();
    }

    public function testThrowExceptionIfGetBuildCLassesStringISCalledWithoutAClass()
    {
        $buildCache = new BuildCache();

        $this->setExpectedException('Exception');

        $buildCache->getBuildClasses();
    }

    public function testGetCachedStringIsEmpty()
    {
        $buildCache = new BuildCache();

        $buildCache->setClasses([
            'stdClass',
        ]);

        $actual = $buildCache->getCachedString();

        $this->assertCount(0, $buildCache->getBuildClasses());

        $this->assertStringStartsWith('// @generatedBy', $actual);
    }

    public function testGetCachedStringIsNotEmpty()
    {
        $buildCache = new BuildCache();

        $buildCache->setClasses([
            'SpeedLoaderTestAsset\Simple\ClassWithBody',
        ]);

        $actual = $buildCache->getCachedString();

        $this->assertCount(1, $buildCache->getBuildClasses());

        $this->assertContains('namespace SpeedLoaderTestAsset', $actual);
    }

    public function testGetCachedClassHasRightOrderWithParent()
    {
        $buildCache = new BuildCache();

        $buildCache->setClasses([
            'SpeedLoaderTestAsset\Simple\FinalClass',
            'SpeedLoaderTestAsset\Simple\AbstractClass',
        ]);

        $buildClasses = $buildCache->getBuildClasses();

        $this->assertCount(2, $buildClasses);

        //Abstract must be loaded before the final class, since its the parent of the final
        $firstClass = array_shift($buildClasses);
        $this->assertEquals($firstClass->getClass()
            ->getName(), 'SpeedLoaderTestAsset\Simple\AbstractClass');

        $secondClass = array_shift($buildClasses);
        $this->assertEquals($secondClass->getClass()
            ->getName(), 'SpeedLoaderTestAsset\Simple\FinalClass');
    }

    public function testClassBuildOrderComplex()
    {
        $buildCache = new BuildCache();

        $buildCache->setClasses([
            'SpeedLoaderTestAsset\Complex\Application',
        ]);

        $buildClasses = $buildCache->getBuildClasses();

        //currently traits are not getting cached
        $this->assertCount(5, $buildClasses);

        $class = array_shift($buildClasses);
        $this->assertEquals($class->getClass()
            ->getName(), 'SpeedLoaderTestAsset\Complex\Vendor1\LoggingInterface');

        $class = array_shift($buildClasses);
        $this->assertEquals($class->getClass()
            ->getName(), 'SpeedLoaderTestAsset\Complex\Vendor2\VendorLoggingInterface');

        $class = array_shift($buildClasses);
        $this->assertEquals($class->getClass()
            ->getName(), 'SpeedLoaderTestAsset\Complex\Vendor2\AbstractApplication');

        $class = array_shift($buildClasses);
        $this->assertEquals($class->getClass()
            ->getName(), 'SpeedLoaderTestAsset\Complex\Vendor2\Application');

        $class = array_shift($buildClasses);
        $this->assertEquals($class->getClass()
            ->getName(), 'SpeedLoaderTestAsset\Complex\Application');
    }
}
