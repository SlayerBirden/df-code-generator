<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Parts\Gets;

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
        $hydratorKey = $this->dataProvider->provide()['hydrator_name'];
        $hydratorFactory = $this->dataProvider->provide()['factory_namespace'] . '\\' .
            $this->dataProvider->provide()['hydrator_factory_name'] . '::class';
        $repoName = $this->dataProvider->provide()['entityClassName'] . 'Repository';
        $repoFactoryName = $this->dataProvider->provide()['factory_namespace'] . '\\' .
            $this->dataProvider->provide()['entityClassName'] .
            'RepositoryFactory::class';

        return [
            'factories' => [
                $hydratorKey => new PhpLiteral($hydratorFactory),
                $repoName => new PhpLiteral($repoFactoryName),
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
