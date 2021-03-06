<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Parts\Update;

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
     * @inheritdoc
     */
    public function getConfig(): array
    {
        $inputFilterKey = $this->dataProvider->provide()['input_filter_name'];
        $hydratorKey = $this->dataProvider->provide()['hydrator_name'];
        $hydratorFactory = $this->dataProvider->provide()['factory_namespace'] . '\\' .
            $this->dataProvider->provide()['hydrator_factory_name'] . '::class';
        $resourceName = $this->dataProvider->provide()['entityClassName'] . 'ResourceMiddleware';
        $resourceFactoryName = $this->dataProvider->provide()['factory_namespace'] . '\\' .
            $this->dataProvider->provide()['entityClassName'] .
            'ResourceMiddlewareFactory::class';
        $inputFilterMiddlewareKey = $this->dataProvider->provide()['input_filter_middleware_name'];
        $inputFilterMiddleware = $this->dataProvider->provide()['factory_namespace'] .
            '\\InputFilterMiddlewareFactory::class';

        return [
            'factories' => [
                $hydratorKey => new PhpLiteral($hydratorFactory),
                $inputFilterKey => new PhpLiteral(
                    '\SlayerBirden\DataFlowServer\Zend\InputFilter\ProxyFilterManagerFactory::class'
                ),
                $resourceName => new PhpLiteral($resourceFactoryName),
                $inputFilterMiddlewareKey => new PhpLiteral($inputFilterMiddleware),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCode(): string
    {
        return self::PART_KEY;
    }
}
