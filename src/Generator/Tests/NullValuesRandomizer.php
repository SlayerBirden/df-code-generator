<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

class NullValuesRandomizer
{
    /**
     * @var float
     */
    private $probabilityOfFilledNull;

    public function __construct(float $probabilityOfFilledNull)
    {
        $this->probabilityOfFilledNull = $probabilityOfFilledNull;
    }

    /**
     * @return bool
     */
    public function ifShouldWrite(): bool
    {
        return rand(0,100)/100 < $this->probabilityOfFilledNull;
    }
}
