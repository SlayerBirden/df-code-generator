<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Catalog\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="products")
 **/
class Product
{
    /**
     * @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue
     * @var integer|null
     **/
    private $id;
    /**
     * @ORM\Column(type="string")
     * @var string
     **/
    private $name;
    /**
     * @ORM\Column(type="string", unique=true)
     * @var string
     **/
    private $sku;
}
