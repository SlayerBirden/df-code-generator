<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers\Add;

use Doctrine\Common\Annotations\AnnotationReader;
use Zend\Code\Reflection\ClassReflection;

abstract class AbstractUniqueFieldsAction extends AbstractAction
{
    /**
     * @var bool
     */
    protected $hasUnique = false;
    /**
     * @var string[]
     */
    protected $uniqueFields = [];

    /**
     * @param string $entityClassName
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(string $entityClassName)
    {
        parent::__construct($entityClassName);
        $this->prepareUnique();
    }

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function prepareUnique(): void
    {
        $reflectionClassName = new ClassReflection($this->entityClassName);
        foreach ($reflectionClassName->getProperties() as $property) {
            /** @var \Doctrine\ORM\Mapping\Column $annotation */
            $annotation = (new AnnotationReader())
                ->getPropertyAnnotation($property, \Doctrine\ORM\Mapping\Column::class);
            if ($annotation->unique) {
                $this->uniqueFields[] = $property->getName();
                $this->hasUnique = true;
            }
        }
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    protected function getParams(): array
    {
        $params = parent::getParams();

        $message = '';
        if ($this->hasUnique) {
            $message = sprintf(
                'Provided %s already exist%s.',
                implode(' or ', $this->uniqueFields),
                count($this->uniqueFields) > 1 ? '' : 's'
            );
        }

        $params['hasUnique'] = $this->hasUnique;
        $params['uniqueIdxMessage'] = $message;

        return $params;
    }
}
