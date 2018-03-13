<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config;

interface DataProviderInterface
{
    public function getRouteFactoryName(): string;

    public function getInputFilterSpec(): array;

    public function getInputFilterName(): string;

    public function getEntitiesSrc(): string;

    public function getCurrentConfig(): array;

    public function getControllerName(string $type): string;

    public function getConfigNameSpace(): string;
}
