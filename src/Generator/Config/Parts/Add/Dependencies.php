<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Parts\Add;

use Nette\PhpGenerator\PhpLiteral;
use SlayerBirden\DFCodeGeneration\Generator\Config\ConfigPartInterface;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderInterface;

final class Dependencies implements ConfigPartInterface
{
    const PART_KEY = 'dependencies';
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
        $inputFilterKey = $this->dataProvider->provide()['input_filter_name'];
        $hydratorKey = $this->dataProvider->provide()['hydrator_name'];
        $hydratorFactory = $this->dataProvider->provide()['factory_namespace'] . '\\' .
            $this->dataProvider->provide()['hydrator_factory_name'] . '::class';

        return array_merge_recursive($current, [
            'factories' => [
                $hydratorKey => new PhpLiteral($hydratorFactory),
                $inputFilterKey => new PhpLiteral(
                    '\SlayerBirden\DataFlowServer\Zend\InputFilter\ProxyFilterManagerFactory::class'
                ),
            ],
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
        return 'getDependenciesConfig';
    }
}
