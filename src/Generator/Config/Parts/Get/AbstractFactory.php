<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Parts\Get;

use SlayerBirden\DFCodeGeneration\Generator\Config\ConfigPartInterface;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderInterface;

final class AbstractFactory implements ConfigPartInterface
{
    const PART_KEY = \Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory::class;

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
        $name = '\\' . $this->getControllerNamespace() . '\Get' . $this->getEntityClassName() . 'Action::class';
        return [
            $name => [
                $this->dataProvider->provide()['hydrator_name'],
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCode(): string
    {
        return self::PART_KEY;
    }

    private function getEntityClassName(): string
    {
        return $this->dataProvider->provide()['entityClassName'];
    }

    private function getControllerNamespace(): string
    {
        return $this->dataProvider->provide()['controller_namespace'];
    }
}
