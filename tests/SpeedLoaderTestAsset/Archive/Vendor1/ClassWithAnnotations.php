<?php
namespace Vendor1;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="annotations")
 */
class ClassWithAnnotations
{

    /**
     * @ORM\Column(name="docId", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     *
     * @var string
     */
    private $id;
}
