<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers\Add;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Zend\Code\Reflection\ClassReflection;

class Add extends AbstractUniqueFieldsAction
{
    protected $template = 'Add.php.twig';

    private $relations = [
        ManyToOne::class,
        ManyToMany::class,
        OneToOne::class,
    ];

    /**
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    protected function getParams(): array
    {
        $params = parent::getParams();

        $reflectionClassName = new ClassReflection($this->entityClassName);
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

    public function getClassName(): string
    {
        return $this->getNs($this->entityClassName) . '\\Add' . $this->getBaseName($this->entityClassName) . 'Action';
    }
}
