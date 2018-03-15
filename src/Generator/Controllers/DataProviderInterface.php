<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers;

use SlayerBirden\DFCodeGeneration\DataProviderInterface as BaseDataProvider;

interface DataProviderInterface extends BaseDataProvider
{
    public function getClassName(string $type): string;
}
