<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Providers\Decorators;

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
     * @throws \ReflectionException
     */
    public function decorate(array $data): array
    {
        $data['hydrator_name'] = $this->getHydratorName();

        return $data;
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    private function getHydratorName(): string
    {
        return Lexer::getBaseName($this->entityClassName) . 'Hydrator';
    }
}
