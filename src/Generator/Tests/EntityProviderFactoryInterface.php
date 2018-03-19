<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

interface EntityProviderFactoryInterface
{
    public function create(string $entityClassName): EntityProviderInterface;
}
