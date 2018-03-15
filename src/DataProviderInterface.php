<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration;

interface DataProviderInterface
{
    public function provide(): array;
}
