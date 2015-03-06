<?php
namespace SpeedLoader;

use Zend\Code\Reflection\ClassReflection;

class BuildCache
{
    private $newLine = "\n";

    private $seen = [];

    private $buildClasses = [];

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
     * @return ClassReflection[]
     */
    private function getOrdererClasses(array $classes)
    {
        $this->seen = [];

        $map = [];
        foreach ($classes as $class) {
            $reflectionClass = new ClassReflection($class);
            $map             = array_merge($map, $this->getClassHierarchy($reflectionClass));
        }

        return $map;
    }

    /**
     *
     * @param  ClassReflection   $class
     * @return ClassReflection[]
     */
    private function getClassHierarchy(ClassReflection $class)
    {
        if (in_array($class->getName(), $this->seen)) {
            return [];
        }

        $this->seen[] = $class->getName();

        $classes = [
            $class,
        ];
        $parent = $class;
        while (($parent = $parent->getParentClass()) && $parent->isUserDefined() && ! in_array($parent->getName(), $this->seen)) {
            $this->seen[] = $parent->getName();
            array_unshift($classes, $parent);
        }

        // $traits = array();
        // foreach ($classes as $c) {
        // foreach (self::resolveDependencies(self::computeTraitDeps($c), $c) as $trait) {
        // if ($trait !== $c) {
        // $traits[] = $trait;
        // }
        // }
        // }

        return array_merge($this->getInterfaces($class), $classes);
    }

    /**
     *
     * @param  ClassReflection   $class
     * @return ClassReflection[]
     */
    private function getInterfaces(ClassReflection $class)
    {
        $classes = [];
        foreach ($class->getInterfaces() as $interface) {
            $classes = array_merge($classes, $this->getInterfaces($interface));
        }

        if ($class->isUserDefined() && $class->isInterface() && ! in_array($class->getName(), $this->seen)) {
            $this->seen[] = $class->getName();
            $classes[]    = $class;
        }

        return $classes;
    }

    /**
     * Concat the given classes
     *
     * @param array $classes
     */
    public function cache(array $classes)
    {
        foreach ($this->getOrdererClasses($classes) as $class) {
            $build = new BuildClass();
            $build->setClass($class);

            if ($build->canBeCached() === true) {
                $this->buildClasses[$class->getName()] = $build;
            }
        }
    }

    /**
     *
     * @return BuildClass[]
     */
    private function getClasses()
    {
        return $this->buildClasses;
    }

    /**
     * Get the string which can be cached
     *
     * @return string
     */
    public function getCacheString()
    {
        $concat = '// @generatedBy ThaDafinser/SpeedLoader' . "\n";
        $concat .= '// @date ' . date('Y-m-d H:i:s') . "\n";
        $concat .= '// @count: ' . $this->getClassCacheCount() . "\n";

        $currentType = null;
        foreach ($this->getClasses() as $class) {
            if ($currentType != $class->getClassType()) {
                $concat .= "\n" . '/**********************' . "\n";
                $concat .= ' * ' . $class->getClassType() . 's' . "\n";
                $concat .= ' **********************/' . "\n";

                $currentType = $class->getClassType();
            }

            $class->setCompressionLevel(BuildClass::COMPRESS_HIGH);

            $concat .= $class->getResult() . $class->getNewLine();
        }

        return $concat;
    }

    /**
     *
     * @return integer
     */
    public function getClassCacheCount()
    {
        return count($this->buildClasses);
    }
}
