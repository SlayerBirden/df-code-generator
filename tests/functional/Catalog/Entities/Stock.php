<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Catalog\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="stock")
 **/
class Stock
{
    /**
     * @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue
     * @var integer|null
     **/
    private $id;
    /**
     * @ORM\Column(type="float")
     * @var float
     **/
    private $qty;
    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     **/
    private $createdAt;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getQty(): float
    {
        return $this->qty;
    }

    /**
     * @param float $qty
     */
    public function setQty(float $qty): void
    {
        $this->qty = $qty;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
