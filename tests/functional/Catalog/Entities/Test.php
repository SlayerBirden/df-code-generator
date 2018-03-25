<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Catalog\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="stock")
 **/
class Test
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @var string
     **/
    private $bar;
    /**
     * @ORM\Column(type="string")
     * @var string
     **/
    private $baz;

    /**
     * @return string
     */
    public function getBar(): string
    {
        return $this->bar;
    }

    /**
     * @return string
     */
    public function getBaz(): string
    {
        return $this->baz;
    }

    /**
     * @param string $baz
     */
    public function setBaz(string $baz): void
    {
        $this->baz = $baz;
    }
}
