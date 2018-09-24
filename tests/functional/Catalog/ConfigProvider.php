<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Catalog;

final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'input_filter_specs' => [
                'StockInputFilter' => $this->getStockInputFilterSpec(),
            ],
            'doctrine' => $this->getDoctrineConfig(),
            'dependencies' => $this->getDependenciesConfig(),
            'validators' => $this->getValidatorsConfig(),
        ];
    }

    private function getStockInputFilterSpec(): array
    {
        return [
            'qty' => [
                'filters' => [
                    [
                        'name' => 'stringtrim',
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'notempty',
                    ],
                    [
                        'name' => 'digits',
                    ],
                ],
            ],
        ];
    }

    private function getDoctrineConfig(): array
    {
        return [
            'entity_managers' => [
                'default' => [
                    'paths' => [
                        'src/Catalog/Entities',
                        'vendor/my_awesome_paths',
                    ],
                ],
            ],
        ];
    }

    private function getDependenciesConfig(): array
    {
        return [
            'factories' => [
                'ProductHydrator' => '\SlayerBirden\DFCodeGeneration\Catalog\Factory\ProductHydratorFactory',
                'SomeAwesomeDep' => '\My\Awesome\Factory',
            ],
        ];
    }

    private function getValidatorsConfig(): array
    {
        return [
            'validators_are_awesome',
        ];
    }
}
