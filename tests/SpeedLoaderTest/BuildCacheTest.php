<?php
namespace SpeedLoaderTest;

use PHPUnit_Framework_TestCase;
use SpeedLoader\BuildCache;

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

    public function testSetGetClasses()
    {
        $buildCache = new BuildCache();
        
        $this->assertInternalType('array', $buildCache->getClasses());
        
        $classes = [
            'stdClass',
            'IntlSomeThing'
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
            'stdClass'
        ]);
        
        $actual = $buildCache->getCachedString();
        
        $this->assertCount(0, $buildCache->getBuildClasses());
        
        $this->assertStringStartsWith('// @generatedBy', $actual);
    }

    public function testGetCachedStringIsNotEmpty()
    {
        $buildCache = new BuildCache();
        
        $buildCache->setClasses([
            'SpeedLoaderTestAsset\Simple\ClassWithBody'
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
            'SpeedLoaderTestAsset\Simple\AbstractClass'
        ]);
        
        $actual = $buildCache->getCachedString();
        
        $this->assertCount(2, $buildCache->getBuildClasses());
        
        $buildClasses = $buildCache->getBuildClasses();
        
        $firstClass = array_shift($buildClasses);
        $this->assertEquals($firstClass->getClass()
            ->getName(), 'SpeedLoaderTestAsset\Simple\AbstractClass');
        
        $secondClass = array_shift($buildClasses);
        $this->assertEquals($secondClass->getClass()
            ->getName(), 'SpeedLoaderTestAsset\Simple\FinalClass');
    }
}
