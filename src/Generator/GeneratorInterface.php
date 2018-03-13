<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator;

interface GeneratorInterface
{
    public function generate(): string;
}
