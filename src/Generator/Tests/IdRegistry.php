<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

use Faker\Factory;

class IdRegistry implements IdHandlerInterface
{
    private $ids = [];
    /**
     * @var \Faker\Generator
     */
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function generateStringId(string $entity): string
    {
        $this->ids[$entity] = $this->faker->unique()->word;

        return $this->ids[$entity];
    }

    public function generateIntId(string $entity, bool $increment): int
    {
        if ($increment) {
            $this->ids[$entity] = isset($this->ids[$entity]) ? ++$this->ids[$entity] : 1;
        } else {
            $this->ids[$entity] = $this->faker->unique()->numberBetween(0,100);
        }

        return $this->ids[$entity];
    }

    /**
     * @param string $entity
     * @return mixed
     */
    public function getId(string $entity)
    {
        return $this->ids[$entity] ?? null;
    }
}
