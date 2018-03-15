<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Factory;

use SlayerBirden\DFCodeGeneration\DataProviderInterface as BaseDataProvider;

interface DataProviderInterface extends BaseDataProvider
{
    public function getClassName(): string;
}
