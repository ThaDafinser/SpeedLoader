<?php
namespace SpeedLoader;

use Zend\Code\Reflection\ClassReflection;

class BuildClass
{
    /**
     *
     * @var ClassReflection
     */
    private $class;

    private $newLine = "\n";

    const COMPRESS_NONE = 0;

    const COMPRESS_LOW = 1;

    const COMPRESS_HIGH = 9;

    private $compressionLevel = self::COMPRESS_NONE;

    /**
     *
     * @param ClassReflection $class
     */
    public function setClass(ClassReflection $class)
    {
        $this->class = $class;
    }

    /**
     *
     * @return ClassReflection
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     *
     * @param string $char
     */
    public function setNewLine($char = "\n")
    {
        $this->newLine = $char;
    }

    /**
     *
     * @return string
     */
    public function getNewLine()
    {
        return $this->newLine;
    }

    /**
     *
     * @param integer $lvl
     */
    public function setCompressionLevel($lvl = self::COMPRESS_LOW)
    {
        $this->compressionLevel = $lvl;
    }

    public function getCompressionLevel()
    {
        return $this->compressionLevel;
    }

    /**
     *
     * @return string
     */
    public function getClassType()
    {
        $class = $this->getClass();

        if ($class->isInterface()) {
            return 'interface';
        } elseif ($class->isTrait()) {
            return 'trait';
        }

        return 'class';
    }

    private function getClassDocBlock()
    {
        $class = $this->getClass();

        $comment = $class->getDocComment();
        if ($comment != '') {
            $comment .= $this->getNewLine();
        }

        return $comment;
    }

    private function getUses()
    {
        $class = $this->getClass();

        $uses = [];
        foreach ($class->getDeclaringFile()->getUses() as $use) {
            // var_dump($use);

            $useString = 'use ' . $use['use'];
            if ($use['as'] !== null) {
                $useString .= ' as ' . $use['as'];
            }
            $useString .= ';';

            $uses[] = $useString;
        }

        return implode($this->getNewLine(), $uses);
    }

    private function getDeclaration()
    {
        $class = $this->getClass();

        $declaration = '';
        if ($class->isAbstract() && ! $class->isInterface() && ! $class->isTrait()) {
            $declaration .= 'abstract ';
        }
        if ($class->isFinal()) {
            $declaration .= 'final ';
        }

        $declaration .= $this->getClassType() . ' ';
        $declaration .= $class->getShortName();

        $interfacesNames = $class->getInterfaceNames();

        /*
         * parent
         */
        $parentClass = $class->getParentClass();
        if ($parentClass instanceof ClassReflection) {
            // using the absolute namespace + classname work, even if the class has an use alias defined (absolute are unique)
            $declaration .= ' extends \\' . $parentClass->getName();

            $interfacesNames = array_diff($interfacesNames, $parentClass->getInterfaceNames());
        }

        /*
         * interfaces
         */
        if (count($interfacesNames) > 0) {
            $interfacesNamesResult = $interfacesNames;
            foreach ($interfacesNames as $key => $interfaceName) {
                $interface             = new ClassReflection($interfaceName);
                $interfacesNamesResult = array_diff($interfacesNamesResult, $interface->getInterfaceNames());
            }

            $declaration .= $class->isInterface() ? ' extends ' : ' implements ';

            // using the absolute namespace + classname work, even if the class has an use alias defined (absolute are unique)
            $declaration .= '\\' . implode(', \\', $interfacesNamesResult);
        }

        return $declaration;
    }

    /**
     * Get the inner body of the class
     *
     * @return string
     */
    private function getBody()
    {
        $class = $this->getClass();

        $body = $class->getContents(false) . $this->getNewLine();

        // relatives need to be replaced
        $dir = dirname($class->getFileName());
        $dir = str_replace('\\', '/', $dir);

        $file = $class->getFileName();
        $file = str_replace('\\', '/', $file);

        $body = str_replace('__DIR__', '\'' . $dir . '\'', $body);
        $body = str_replace('__FILE__', '\'' . $file . '\'', $body);

        return $body;
    }

    /**
     *
     * @param string $code
     *
     * @return string
     */
    private function compressCode($code)
    {
        switch ($this->getCompressionLevel()) {

            case self::COMPRESS_LOW:
                // @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/ClassLoader/ClassCollectionLoader.php#L210-L217
                $code = preg_replace([
                    '/^\s+/m',
                    '/\s+$/m',
                    '/([\n\r]+ *[\n\r]+)+/',
                    '/[ \t]+/',
                ], [
                    '',
                    '',
                    "\n",
                    ' ',
                ], $code);
                break;

            case self::COMPRESS_HIGH:
                $tmpName = tempnam(sys_get_temp_dir(), 'SpeedLoader');
                file_put_contents($tmpName, '<?php ' . $code);

                $code = php_strip_whitespace($tmpName);
                $code = substr($code, 6);

                unlink($tmpName);
                break;
        }

        return $code;
    }

    public function canBeCached()
    {
        $class = $this->getClass();

        if ($class->isInternal() === true) {
            return false;
        }

        $body     = $this->getBody();
        $docBlock = $class->getDocBlock();

        /*
         * Problem related to Doctrine...
         *
         * @see Doctrine\Common\Annotations\AnnotationRegistry::registerFile() @error Doctrine\Common\Annotations\AnnotationException' with message '[Semantical Error] The annotation "@ORM\Entity" in class LispUser\Entity\User was never imported. Did you maybe forget to add a "use" statement for this annotation?
         */
        if (strpos($class->getName(), 'Doctrine') === 0) {
            return false;
        }
        if (strpos($class->getName(), 'Gedmo') === 0) {
            return false;
        }

        if (is_object($docBlock) && stripos($docBlock->getContents(), '@ORM') !== false) {
            return false;
        }
        if (stripos($body, '@ORM') !== false) {
            return false;
        }

        return true;
    }

    /**
     *
     * @throws \Exception
     * @return string
     */
    public function getResult()
    {
        $class = $this->getClass();

        $body     = $this->getBody();
        $docBlock = $class->getDocBlock();

        $string = '';
        $string .= 'namespace ' . $class->getNamespaceName() . ' ';
        $string .= '{' . $this->getNewLine();
        $string .= $this->getUses() . $this->getNewLine();
        // always include, since there could be annotations!
        $string .= $this->getClassDocBlock();
        $string .= $this->getDeclaration() . $this->getNewLine();
        $string .= $this->getBody();
        $string .= '}';

        return $this->compressCode($string);
    }
}
