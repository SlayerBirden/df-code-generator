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
     * Is not allows in this context
     *
     * {@inheritdoc}
     */
    public function getConfig(array $current = []): array
    {
        throw new \LogicException('Invalid usage of getMethodName in ' . self::class);
    }

    /**
     * @inheritdoc
     */
    public function getCode(): string
    {
        return self::PART_KEY;
    }

    /**
     * Is not allows in this context
     *
     * {@inheritdoc}
     */
    public function getMethodName(): string
    {
        throw new \LogicException('Invalid usage of getMethodName in ' . self::class);
    }

    /**
     * @return ConfigPartInterface[]
     */
    public function getParts(): array
    {
        return $this->parts;
    }
}
