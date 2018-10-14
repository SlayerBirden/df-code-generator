<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Command;

use Doctrine\ORM\Mapping as ORM;

final class Hubby
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @var string
     */
    private $hubbyId;
    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $name;
}
