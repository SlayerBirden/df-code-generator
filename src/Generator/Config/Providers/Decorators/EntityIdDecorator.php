<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Providers\Decorators;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderDecoratorInterface;
use Zend\Filter\Word\UnderscoreToCamelCase;

final class EntityIdDecorator implements DataProviderDecoratorInterface
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
        $reflectionClassName = new \ReflectionClass($this->entityClassName);
        foreach ($reflectionClassName->getProperties() as $property) {
            /** @var Id $idAnnotation */
            $idAnnotation = (new AnnotationReader())
                ->getPropertyAnnotation($property, Id::class);
            /** @var Column $columnAnnotation */
            $columnAnnotation = (new AnnotationReader())
                ->getPropertyAnnotation($property, Column::class);
            if ($idAnnotation && $columnAnnotation) {
                $data['idName'] = $property->getName();
                $data['idType'] = $columnAnnotation->type;
                $data['idRegexp'] = $this->getRegexpBasedOnType($data['idType']);
                $data['idGetter'] = 'get' . (new UnderscoreToCamelCase())->filter($data['idName']);
                $data['invalidId'] = $this->getInvalidIdBasedOnType($data['idType']);
            }
        }

        return $data;
    }

    private function getRegexpBasedOnType(string $type): string
    {
        switch ($type) {
            case 'string':
                return '\w+';
            default: // int
                return '\d+';
        }
    }

    private function getInvalidIdBasedOnType(string $type)
    {
        $faker = \Faker\Factory::create();
        switch ($type) {
            case 'string':
                return $faker->numberBetween(0, 100);
            default: // int
                return $faker->word;
        }
    }
}
