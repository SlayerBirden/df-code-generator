<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Factory;

interface DataProviderInterface
{
    public function getRoutesDelegatorNameSpace(): string;
    public function getControllerNameSpace(): string;
    public function getShortName(): string;
}
