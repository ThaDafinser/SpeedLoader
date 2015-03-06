<?php
namespace SpeedLoaderTest;

use PHPUnit_Framework_TestCase;
use SpeedLoader\BuildCache;

// use Zend\Code\Reflection\ClassReflection;

/**
 * @covers \SpeedLoader\BuildCache
 */
class BuildCacheTest extends PHPUnit_Framework_TestCase
{
    // private function invokeMethod(&$object, $methodName, array $parameters = array())
    // {
    // $reflection = new \ReflectionClass(get_class($object));
    // $method = $reflection->getMethod($methodName);
    // $method->setAccessible(true);

    // return $method->invokeArgs($object, $parameters);
    // }

    // private function getClassReflection($name)
    // {
    // $classRef = $this->getMockBuilder('Zend\Code\Reflection\ClassReflection')
    // ->setConstructorArgs([
    // $name
    // ])
    // ->getMock();

    // return $classRef;
    // }
    public function testSetGetNewLine()
    {
        $buildCache = new BuildCache();

        $this->assertEquals("\n", $buildCache->getNewLine());

        $buildCache->setNewLine("\r\n");
        $this->assertEquals("\r\n", $buildCache->getNewLine());
    }
}
