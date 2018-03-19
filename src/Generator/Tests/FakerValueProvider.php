<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

use Faker\Factory;

class FakerValueProvider implements ValueProviderInterface
{
    /**
     * @var string
     */
    private $entityClassName;
    /**
     * @var IdHandlerInterface
     */
    private $idHandler;

    public function __construct(string $entityClassName, IdHandlerInterface $idHandler)
    {
        $this->entityClassName = $entityClassName;
        $this->idHandler = $idHandler;
    }

    public function getValue(string $type, bool $generated)
    {
        $faker = Factory::create();
        $value = null;
        switch ($type) {
            case 'string':
                $value = $faker->word;
                break;
            case 'integer':
                $value = $this->idHandler->generateIntId($this->entityClassName, $generated);
                break;
            case 'float':
                $value = $faker->randomFloat(2, 1, 20);
                break;
            case 'datetime':
                $value = $faker->dateTime();
                break;
        }

        return $value;
    }
}
