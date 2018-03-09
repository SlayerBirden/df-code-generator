<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers;

use SlayerBirden\DFCodeGeneration\Generator\NamingTrait as BaseNamingTrait;

trait NamingTrait
{
    use BaseNamingTrait;

    protected function getNs(string $entityClassName): string
    {
        $parts = explode('\\', $entityClassName);
        array_splice($parts, -2); // Entities\Model

        $parts[] = 'Controller';

        return ltrim(implode('\\', $parts), '\\');
    }
}
