<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

final class ReflectionInputFilter
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
        $reflectionClassName = new \ReflectionClass($this->entityClassName);
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
            if ($annotation->type === 'datetime') {
                if (empty($inputFilter[$property->getName()]['validators'])) {
                    $inputFilter[$property->getName()]['validators'] = [];
                }
                $inputFilter[$property->getName()]['validators'][] = [
                    'name' => 'datetime',
                ];
            }
        }
        return $inputFilter;
    }
}
