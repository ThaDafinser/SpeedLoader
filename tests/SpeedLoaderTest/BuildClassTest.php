<?php
namespace SpeedLoaderTest;

use PHPUnit_Framework_TestCase;
use SpeedLoader\BuildClass;
use Zend\Code\Reflection\ClassReflection;

/**
 * @covers \SpeedLoader\BuildClass
 */
class BuildClassTest extends PHPUnit_Framework_TestCase
{
    private function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method     = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    private function getClassReflection($name)
    {
        $classRef = $this->getMockBuilder('Zend\Code\Reflection\ClassReflection')
            ->setConstructorArgs([
            $name,
        ])
            ->getMock();

        return $classRef;
    }

    public function testSetGetClass()
    {
        $classRef = $this->getMockBuilder('Zend\Code\Reflection\ClassReflection')
            ->disableOriginalConstructor()
            ->getMock();

        $buildClass = new BuildClass();
        $this->assertNull($buildClass->getClass());

        $buildClass->setClass($classRef);
        $this->assertSame($classRef, $buildClass->getClass());
    }

    public function testSetGetNewLine()
    {
        $buildClass = new BuildClass();

        $this->assertEquals("\n", $buildClass->getNewLine());

        $buildClass->setNewLine("\r\n");
        $this->assertEquals("\r\n", $buildClass->getNewLine());
    }

    public function testSetGetCompressionLevel()
    {
        $buildClass = new BuildClass();

        $this->assertEquals(BuildClass::COMPRESS_NONE, $buildClass->getCompressionLevel());

        $buildClass->setCompressionLevel(BuildClass::COMPRESS_HIGH);
        $this->assertEquals(BuildClass::COMPRESS_HIGH, $buildClass->getCompressionLevel());
    }

    public function testSetGetClassType()
    {
        /*
         * Interface
         */
        $classRef = $this->getClassReflection('SplHeap');
        $classRef->expects($this->any())
            ->method('isInterface')
            ->willReturn(true);

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);

        $this->assertEquals('interface', $buildClass->getClassType());

        /*
         * Trait
         */
        $classRef = $this->getClassReflection('SplHeap');
        $classRef->expects($this->any())
            ->method('isInterface')
            ->willReturn(false);
        $classRef->expects($this->any())
            ->method('isTrait')
            ->willReturn(true);

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);

        $this->assertEquals('trait', $buildClass->getClassType());

        /*
         * Default
         */
        $classRef = $this->getClassReflection('SplHeap');
        $classRef->expects($this->any())
            ->method('isInterface')
            ->willReturn(false);
        $classRef->expects($this->any())
            ->method('isTrait')
            ->willReturn(false);

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);

        $this->assertEquals('class', $buildClass->getClassType());
    }

    public function testGetClassDocBlock()
    {
        /*
         * empty
         */
        $classRef = new ClassReflection('SpeedLoaderTestAsset\Simple\NoDocBlock');

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);

        $result = $this->invokeMethod($buildClass, 'getClassDocBlock');
        $this->assertEquals('', $result);

        /*
         * not empty
         */
        $classRef = new ClassReflection('SpeedLoaderTestAsset\Simple\ClassDocBlock');

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);

        $expected = '/**
 * My doc
 */
';
        $result = $this->invokeMethod($buildClass, 'getClassDocBlock');
        $this->assertEquals($expected, $result);
    }

    public function testGetUses()
    {
        /*
         * no uses
         */
        $classRef = new ClassReflection('SpeedLoaderTestAsset\Simple\ClassDocBlock');

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);

        $result = $this->invokeMethod($buildClass, 'getUses');
        $this->assertEquals('', $result);

        /*
         * two uses
         */
        $classRef = new ClassReflection('SpeedLoaderTestAsset\Simple\ClassWithUses');

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);

        $expected = 'use stdClass;
use SplFileInfo as Test;
use SplPriorityQueue;';
        $result = $this->invokeMethod($buildClass, 'getUses');
        $this->assertEquals($expected, $result);
    }

    public function testGetDeclaration()
    {
        /*
         * abstract
         */
        $classRef = new ClassReflection('SpeedLoaderTestAsset\Simple\AbstractClass');

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);

        $result = $this->invokeMethod($buildClass, 'getDeclaration');
        $this->assertEquals('abstract class AbstractClass', $result);

        /*
         * final + extend
         */
        $classRef = new ClassReflection('SpeedLoaderTestAsset\Simple\FinalClass');

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);

        $result = $this->invokeMethod($buildClass, 'getDeclaration');
        $this->assertEquals('final class FinalClass extends \SpeedLoaderTestAsset\Simple\AbstractClass', $result);

        /*
         * implements
         */
        $classRef = new ClassReflection('SpeedLoaderTestAsset\Simple\ClassWithInterface');

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);

        $result = $this->invokeMethod($buildClass, 'getDeclaration');
        $this->assertEquals('class ClassWithInterface implements \SpeedLoaderTestAsset\Simple\SingleInterface', $result);
    }

    public function testGetBody()
    {
        $classRef = new ClassReflection('SpeedLoaderTestAsset\Simple\ClassWithBody');

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);

        $result = $this->invokeMethod($buildClass, 'getBody');
        $this->assertEquals('{ public function test(){} }' . "\n", $result);
    }

    public function testGetBodyReplaceDir()
    {
        $classRef = new ClassReflection('SpeedLoaderTestAsset\Simple\ClassWithBodyAndDir');

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);

        $result = $this->invokeMethod($buildClass, 'getBody');
        $this->assertEquals('{ public function test(){ \'' . str_replace('\\', '/', __DIR__) . '/asset/Simple\'; } }' . "\n", $result);
    }

    public function testGetBodyReplaceFile()
    {
        $classRef = new ClassReflection('SpeedLoaderTestAsset\Simple\ClassWithBodyAndFile');

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);

        $result = $this->invokeMethod($buildClass, 'getBody');
        $this->assertEquals('{ public function test(){ \'' . str_replace('\\', '/', $classRef->getFileName()) . '\'; } }' . "\n", $result);
    }

    /**
     * @covers SpeedLoader\BuildClass::compressCode
     */
    public function testCompressCodeNone()
    {
        $classRef = new ClassReflection('SpeedLoaderTestAsset\Simple\ClassWithBody');

        $buildClass = new BuildClass();

        $content = str_replace('<?php' . "\n", '', file_get_contents($classRef->getFileName()));
        $result  = $this->invokeMethod($buildClass, 'compressCode', [
            $content,
        ]);

        $this->assertEquals($content, $result);
    }

    /**
     * @covers SpeedLoader\BuildClass::compressCode
     */
    public function testCompressCodeLow()
    {
        $buildClass = new BuildClass();
        $buildClass->setCompressionLevel(BuildClass::COMPRESS_LOW);

        $content = '
namespace Test;

class Bla{
    public function test(){}
}
';
        $result = $this->invokeMethod($buildClass, 'compressCode', [
            $content,
        ]);

        $expected = 'namespace Test;
class Bla{
public function test(){}
}';

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers SpeedLoader\BuildClass::compressCode
     */
    public function testCompressCodeHigh()
    {
        $classRef = new ClassReflection('SpeedLoaderTestAsset\Simple\ClassToCompress');

        $buildClass = new BuildClass();
        $buildClass->setCompressionLevel(BuildClass::COMPRESS_HIGH);

        $content = str_replace('<?php' . "\n", '', file_get_contents($classRef->getFileName()));

        $result = $this->invokeMethod($buildClass, 'compressCode', [
            $content,
        ]);

        $expected = php_strip_whitespace($classRef->getFileName());
        $expected = str_replace('<?php' . "\n", '', $expected);

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers SpeedLoader\BuildClass::canBeCached
     */
    public function testCanBeCached()
    {
        /*
         * isInternal
         */
        $classRef = $this->getClassReflection('SplHeap');
        $classRef->expects($this->any())
            ->method('isInternal')
            ->willReturn(true);

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);
        $this->assertFalse($buildClass->canBeCached());

        /*
         * Doctrine
         */
        $classRef = $this->getClassReflection('SplHeap');
        $classRef->expects($this->any())
            ->method('isInternal')
            ->willReturn(false);
        $classRef->expects($this->any())
            ->method('getName')
            ->willReturn('Doctrine\ORM\MyClass');

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);
        $this->assertFalse($buildClass->canBeCached());

        /*
         * Gedmo
         */
        $classRef = $this->getClassReflection('SplHeap');
        $classRef->expects($this->any())
            ->method('isInternal')
            ->willReturn(false);
        $classRef->expects($this->any())
            ->method('getName')
            ->willReturn('Gedmo\ORM\MyClass');

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);
        $this->assertFalse($buildClass->canBeCached());

        /*
         * Pass
         */
        $classRef = $this->getClassReflection('SplHeap');
        $classRef->expects($this->any())
            ->method('isInternal')
            ->willReturn(false);
        $classRef->expects($this->any())
            ->method('getName')
            ->willReturn('Vendor\MyClass');

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);
        $this->assertTrue($buildClass->canBeCached());
    }

    /**
     * @covers SpeedLoader\BuildClass::getResult
     */
    public function testGetResult()
    {
        $classRef = new ClassReflection('SpeedLoaderTestAsset\Simple\ClassWithBody');

        $buildClass = new BuildClass();
        $buildClass->setClass($classRef);
        $buildClass->setCompressionLevel(BuildClass::COMPRESS_NONE);

        $result = $this->invokeMethod($buildClass, 'getResult');

        $expected = 'namespace SpeedLoaderTestAsset\Simple {

class ClassWithBody
{ public function test(){} }
}';

        $this->assertEquals($expected, $result);
    }
}
