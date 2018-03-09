<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator;

trait NamingTrait
{
    protected function getBaseName(string $entityClassName): string
    {
        $parts = explode('\\', $entityClassName);
        return end($parts);
    }
}
