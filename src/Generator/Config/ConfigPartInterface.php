<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config;

interface ConfigPartInterface
{
    /**
     * Get config array for current part
     *
     * @param array $current
     * @return array
     */
    public function getConfig(array $current = []): array;

    /**
     * Part Code
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * Method name that is used to get config
     *
     * @return string
     */
    public function getMethodName(): string;
}
