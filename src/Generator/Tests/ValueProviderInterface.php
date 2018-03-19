<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

interface ValueProviderInterface
{
    public function getValue(string $type, bool $generated);
}
