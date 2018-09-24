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
     * @inheritdoc
     */
    public function getConfig(): array
    {
        return $this->dataProvider->provide()['input_filter_spec'];
    }

    /**
     * @inheritdoc
     */
    public function getCode(): string
    {
        return $this->dataProvider->provide()['input_filter_name'];
    }
}
