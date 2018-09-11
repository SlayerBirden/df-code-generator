<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config;

interface ArrayConfigPartInterface
{
    /**
     * @return ConfigPartInterface[]
     */
    public function getParts(): array;
}
