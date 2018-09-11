<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers\Providers\Decorators;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderDecoratorInterface;
use Zend\Code\Reflection\ClassReflection;

class RelationsProviderDecorator implements DataProviderDecoratorInterface
{
    private $relations = [
        ManyToOne::class,
        ManyToMany::class,
        OneToOne::class,
    ];
    private $hasRelations = false;
    /**
     * @var string
     */
    private $entityClassName;

    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function prepareRelations(): void
    {
        $reflectionClassName = new ClassReflection($this->entityClassName);
        foreach ($reflectionClassName->getProperties() as $property) {
            foreach ($this->relations as $type) {
                $annotation = (new AnnotationReader())
                    ->getPropertyAnnotation($property, $type);
                if ($annotation) {
                    $this->hasRelations = true;
                    break;
                }
            }
        }
    }

    /**
     * @param array $data
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function decorate(array $data): array
    {
        $this->prepareRelations();

        if ($this->hasRelations) {
            $data['dataRelationship'] = '//TODO process data relationship';
        }

        return $data;
    }
}
