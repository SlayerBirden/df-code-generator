<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Parts;

use SlayerBirden\DFCodeGeneration\Generator\Config\ConfigPartInterface;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderInterface;

final class Doctrine implements ConfigPartInterface
{
    const PART_KEY = 'doctrine';

    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    public function __construct(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    public function getConfig(): array
    {
        return [
            'entity_managers' => [
                'default' => [
                    'paths' => [
                        $this->dataProvider->provide()['entities_src'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Part Code
     *
     * @return string
     */
    public function getCode(): string
    {
        return self::PART_KEY;
    }
}
