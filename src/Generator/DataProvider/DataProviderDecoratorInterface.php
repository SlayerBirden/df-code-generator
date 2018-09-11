<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\DataProvider;

interface DataProviderDecoratorInterface
{
    public function decorate(array $data): array;
}
