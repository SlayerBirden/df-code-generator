<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Providers\Decorators;

use SlayerBirden\DFCodeGeneration\Generator\Config\ReflectionInputFilter;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderDecoratorInterface;
use SlayerBirden\DFCodeGeneration\Util\Lexer;

final class InputFilterDecorator implements DataProviderDecoratorInterface
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
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function decorate(array $data): array
    {
        $data['input_filter_spec'] = $this->getInputFilterSpec();
        $data['input_filter_name'] = $this->getInputFilterName();

        return $data;
    }

    /**
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function getInputFilterSpec(): array
    {
        return (new ReflectionInputFilter($this->entityClassName))->getSpec();
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    private function getInputFilterName(): string
    {
        return Lexer::getBaseName($this->entityClassName) . 'InputFilter';
    }
}
