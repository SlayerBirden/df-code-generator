<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Parts\Add\InputFilter;

use SlayerBirden\DFCodeGeneration\Generator\Config\ConfigPartInterface;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderInterface;

final class Entity implements ConfigPartInterface
{
    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    public function __construct(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * Get config array for current part
     *
     * @param array $current
     * @return array
     */
    public function getConfig(array $current = []): array
    {
        return $this->dataProvider->provide()['input_filter_spec'];
    }

    /**
     * Part Code
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->dataProvider->provide()['input_filter_name'];
    }

    /**
     * Method name that is used to get config
     *
     * @return string
     */
    public function getMethodName(): string
    {
        return 'get' . $this->getCode() . 'Spec';
    }
}
