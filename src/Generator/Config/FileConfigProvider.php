<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config;

final class FileConfigProvider implements CurrentConfigProviderInterface
{
    /**
     * Storage for configs based on name
     *
     * @var array
     */
    private $configs = [];

    public function getCurrentConfig(string $className): array
    {
        if (!isset($this->configs[$className])) {
            if (class_exists($className)) {
                $this->configs[$className] = (new $className())();
            } else {
                $this->configs[$className] = [];
            }
        }

        return $this->configs[$className];
    }

    /**
     * @inheritDoc
     */
    public function setCurrentConfig(string $className, array $config): void
    {
        $this->configs[$className] = $config;
    }
}
