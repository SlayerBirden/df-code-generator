<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests\Api;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

final class ReflectionEntitySpecProvider implements EntitySpecProviderInterface
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
        $spec = [];
        $reflectionClassName = new \ReflectionClass($this->entityClassName);
        foreach ($reflectionClassName->getProperties() as $property) {
            /** @var Column $annotation */
            $annotation = (new AnnotationReader())
                ->getPropertyAnnotation($property, Column::class);
            if (!$annotation) {
                continue;
            }
            $idAnnotation = (new AnnotationReader())
                ->getPropertyAnnotation($property, Id::class);
            $generated = (new AnnotationReader())
                ->getPropertyAnnotation($property, GeneratedValue::class);
            $spec[$property->getName()] = [
                'required' => !$annotation->nullable,
                'type' => $annotation->type,
                'is_id' => $idAnnotation !== null,
                'is_unique' => $annotation->unique,
                'is_generated' => $generated !== null,
            ];
        }
        return $spec;
    }
}
