<?php
namespace Vendor1;

use Vendor2\AbstractClass;

class ClassWithFileConstant extends AbstractClass implements \Vendor2\SpecialInterface
{

    const BLUBB = __FILE__;
}
