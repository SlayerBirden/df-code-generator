<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Parts\Add;

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
    public function getConfig(array $current = []): array
    {
        $name = '\\' . $this->getControllerNamespace() . '\Add' . $this->getEntityClassName() . 'Action::class';
        return array_merge_recursive($current, [
            $name => [
                new PhpLiteral('\SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry::class'),
                'DbConfigHydrator',
                'ConfigInputFilter',
                LoggerInterface::class,
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getCode(): string
    {
        return self::PART_KEY;
    }

    /**
     * @inheritdoc
     */
    public function getMethodName(): string
    {
        return 'getAbstractFactoryConfig';
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