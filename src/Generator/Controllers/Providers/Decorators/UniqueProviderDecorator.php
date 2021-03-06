<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers\Providers\Decorators;

use Doctrine\Common\Annotations\AnnotationReader;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderDecoratorInterface;

final class UniqueProviderDecorator implements DataProviderDecoratorInterface
{
    /**
     * @var bool
     */
    private $hasUnique = false;
    /**
     * @var string[]
     */
    private $uniqueFields = [];
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
    private function prepareUnique(): void
    {
        $reflectionClassName = new \ReflectionClass($this->entityClassName);
        foreach ($reflectionClassName->getProperties() as $property) {
            /** @var \Doctrine\ORM\Mapping\Column $annotation */
            $annotation = (new AnnotationReader())
                ->getPropertyAnnotation($property, \Doctrine\ORM\Mapping\Column::class);
            if ($annotation && $annotation->unique) {
                $this->uniqueFields[$property->getName()] = $property->getName();
                $this->hasUnique = true;
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
        $this->prepareUnique();

        $message = '';
        if ($this->hasUnique) {
            $message = sprintf(
                'Provided %s already exist%s.',
                implode(' or ', $this->uniqueFields),
                count($this->uniqueFields) > 1 ? '' : 's'
            );
        }

        $data['hasUnique'] = $this->hasUnique;
        $data['uniqueIdxMessage'] = $message;

        return $data;
    }
}
