<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config;

interface ConfigPartInterface
{
    /**
     * Get config array for current part
     *
     * @return array
     */
    public function getConfig(): array;

    /**
     * Part Code
     *
     * @return string
     */
    public function getCode(): string;
}
