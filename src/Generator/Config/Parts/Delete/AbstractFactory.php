<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Parts\Delete;

use Nette\PhpGenerator\PhpLiteral;
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
        $name = '\\' . $this->getControllerNamespace() . '\Delete' . $this->getEntityClassName() . 'Action::class';
        $hydratorName = $this->dataProvider->provide()['hydrator_name'];
        return [
            $name => [
                new PhpLiteral('\SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry::class'),
                $hydratorName,
                LoggerInterface::class,
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
