<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Parts\Gets;

use Psr\Log\LoggerInterface;
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
        $pluralEntityName = $this->dataProvider->provide()['pluralEntityName'];
        $name = '\\' . $this->getControllerNamespace() . '\Get' . $pluralEntityName . 'Action::class';
        return [
            $name => [
                $this->dataProvider->provide()['entityClassName'] . 'Repository',
                LoggerInterface::class,
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

    private function getControllerNamespace(): string
    {
        return $this->dataProvider->provide()['controller_namespace'];
    }
}
