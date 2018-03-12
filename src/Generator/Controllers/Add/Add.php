<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers\Add;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;

class Add extends AbstractUniqueFieldsAction
{
    protected $template = 'Add.php.twig';

    private $relations = [
        ManyToOne::class,
        ManyToMany::class,
        OneToOne::class,
    ];

    protected function getParams(): array
    {
        $params = parent::getParams();

        $reflectionClassName = new \ReflectionClass($this->entityClassName);
        foreach ($reflectionClassName->getProperties() as $property) {
            foreach ($this->relations as $type) {
                $annotation = (new AnnotationReader())
                    ->getPropertyAnnotation($property, $type);
                if ($annotation) {
                    $params['dataRelationship'] = '//TODO process data relationship';
                }
            }
        }

        return $params;
    }
}
