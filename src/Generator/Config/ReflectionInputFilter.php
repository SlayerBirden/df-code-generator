<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Zend\Code\Reflection\ClassReflection;

class ReflectionInputFilter
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
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function getSpec(): array
    {
        $inputFilter = [];
        $reflectionClassName = new ClassReflection($this->entityClassName);
        foreach ($reflectionClassName->getProperties() as $property) {
            $generated = (new AnnotationReader())
                ->getPropertyAnnotation($property, GeneratedValue::class);
            if ($generated) {
                continue;
            }
            /** @var Column $annotation */
            $annotation = (new AnnotationReader())
                ->getPropertyAnnotation($property, Column::class);
            if (!$annotation) {
                continue;
            }
            $inputFilter[$property->getName()] = [
                'required' => !$annotation->nullable,
                'filters' => [
                    [
                        'name' => 'stringtrim',
                    ]
                ],
            ];
            if (!$annotation->nullable) {
                $inputFilter[$property->getName()]['validators'] = [
                    [
                        'name' => 'notempty',
                    ],
                ];
            }
        }
        return $inputFilter;
    }
}
