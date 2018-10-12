<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Factory\Providers\Decorators;

use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderDecoratorInterface;
use SlayerBirden\DFCodeGeneration\Util\Lexer;

final class HydratorDecorator implements DataProviderDecoratorInterface
{
    /**
     * @var string
     */
    private $entityClassName;

    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }

    /**
     * @param array $data
     * @return array
     */
    public function decorate(array $data): array
    {
        $data['hydrator_factory_name'] = $this->getHydratorFactoryName();

        return $data;
    }

    /**
     * @return string
     */
    private function getHydratorFactoryName(): string
    {
        return Lexer::getBaseName($this->entityClassName) . 'HydratorFactory';
    }
}
