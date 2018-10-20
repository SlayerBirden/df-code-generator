<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests\Api;

interface EntitySpecProviderInterface
{
    public function getSpec(): array;
}
