<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Util;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Zend\Code\Reflection\ClassReflection;

final class Entity
{
    /**
     * @param string $entity
     * @return null|string
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public static function getEntityIdName(string $entity): ?string
    {
        $name = '';
        $reflectionClassName = new ClassReflection($entity);
        foreach ($reflectionClassName->getProperties() as $property) {
            /** @var Id $id */
            $id = (new AnnotationReader())
                ->getPropertyAnnotation($property, Id::class);
            if (!empty($id)) {
                $name = $property->getName();
                break;
            }
        }

        return $name;
    }

    /**
     * @param string $entity
     * @return string
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public static function getEntityIdType(string $entity): string
    {
        $type = '';
        $reflectionClassName = new ClassReflection($entity);
        foreach ($reflectionClassName->getProperties() as $property) {
            /** @var Id $id */
            $id = (new AnnotationReader())
                ->getPropertyAnnotation($property, Id::class);
            if (!empty($id)) {
                /** @var Column $column */
                $column = (new AnnotationReader())
                    ->getPropertyAnnotation($property, Column::class);
                if ($column) {
                    $type = $column->type;
                }
                break;
            }
        }

        return $type;
    }
}
