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

    public function getConfig(array $current = []): array
    {
        $config = [
            'entity_managers' => [
                'default' => [
                    'paths' => [
                        $this->dataProvider->provide()['entities_src'],
                    ],
                ],
            ],
        ];

        return array_merge_recursive($current, $config);
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

    /**
     * Method name that is used to get config
     *
     * @return string
     */
    public function getMethodName(): string
    {
        return 'getDoctrineConfig';
    }
}
