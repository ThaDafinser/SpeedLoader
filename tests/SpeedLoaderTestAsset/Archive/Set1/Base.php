<?php
namespace Set1;

use Exception, ErrorException;
use Zend\Stdlib\AbstractOptions;
use stdClass as blubb;

/**
 * My super class
 *
 * @author kecmar
 *        
 */
class Base extends AbstractBase
{
    use BaseTrait;

    /**
     * My method works good
     *
     * @return boolean
     */
    public function myMethod()
    {
        return true;
    }

    private function error()
    {
        throw new Exception();
    }
}
