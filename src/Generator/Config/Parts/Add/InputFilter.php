<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Parts\Add;

use SlayerBirden\DFCodeGeneration\Generator\Config\ArrayConfigPartInterface;
use SlayerBirden\DFCodeGeneration\Generator\Config\ConfigPartInterface;

final class InputFilter implements ConfigPartInterface, ArrayConfigPartInterface
{
    const PART_KEY = 'input_filter_specs';
    /**
     * @var ConfigPartInterface[]
     */
    private $parts;

    public function __construct(ConfigPartInterface ...$parts)
    {
        $this->parts = $parts;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        $partsConfig = [];
        foreach ($this->parts as $configPart) {
            $partsConfig[$configPart->getCode()] = $configPart->getConfig();
        }

        return $partsConfig;
    }

    /**
     * @inheritdoc
     */
    public function getCode(): string
    {
        return self::PART_KEY;
    }

    /**
     * @return ConfigPartInterface[]
     */
    public function getParts(): array
    {
        return $this->parts;
    }
}
