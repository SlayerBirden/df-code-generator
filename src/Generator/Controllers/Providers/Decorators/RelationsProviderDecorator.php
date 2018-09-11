<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers\Providers\Decorators;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderDecoratorInterface;

final class RelationsProviderDecorator implements DataProviderDecoratorInterface
{
    private $relations = [
        ManyToOne::class,
        ManyToMany::class,
        OneToOne::class,
        OneToMany::class,
    ];
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
    private function getRelations(): array
    {
        $fields = [];

        $reflectionClassName = new \ReflectionClass($this->entityClassName);
        foreach ($reflectionClassName->getProperties() as $property) {
            foreach ($this->relations as $type) {
                /** @var ManyToMany|ManyToOne|OneToMany|OneToOne $annotation */
                $annotation = (new AnnotationReader())
                    ->getPropertyAnnotation($property, $type);
                if ($annotation) {
                    $fields[$property->getName()] = [
                        'type' => $type,
                        'target' => $annotation->targetEntity,
                    ];
                }
            }
        }

        return $fields;
    }

    /**
     * @param array $data
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function decorate(array $data): array
    {
        $data['relations'] = $this->getRelations();
        $data['single_deps'] = [];
        $data['multi_deps'] = [];

        // sort into brackets
        foreach ($data['relations'] as $column => $columnData) {
            $type = $columnData['type'];
            $target = $columnData['target'];
            if ($type === ManyToOne::class || $type === OneToOne::class) {
                $data['single_deps'][$column] = $target;
            } elseif ($type === ManyToMany::class || $type == OneToMany::class) {
                $data['multi_deps'][$column] = $target;
            }
        }

        return $data;
    }
}
