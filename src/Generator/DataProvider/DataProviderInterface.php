<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\DataProvider;

interface DataProviderInterface
{
    public function provide(): array;
}
