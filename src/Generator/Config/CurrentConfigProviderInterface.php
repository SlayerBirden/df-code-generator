<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config;

interface CurrentConfigProviderInterface
{
    /**
     * Get current config
     *
     * @param string $className
     * @return array
     */
    public function getCurrentConfig(string $className): array;

    /**
     * Set current config
     *
     * @param string $className
     * @param array $config
     * @return void
     */
    public function setCurrentConfig(string $className, array $config): void;
}
