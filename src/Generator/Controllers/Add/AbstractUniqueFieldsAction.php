<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers\Add;

use Doctrine\Common\Annotations\AnnotationReader;

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

    public function __construct(string $entityClassName)
    {
        parent::__construct($entityClassName);
        $this->prepareUnique();
    }

    private function prepareUnique(): void
    {
        $reflectionClassName = new \ReflectionClass($this->entityClassName);
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
