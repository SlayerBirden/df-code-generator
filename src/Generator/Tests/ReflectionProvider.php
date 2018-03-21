<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use SlayerBirden\DFCodeGeneration\Generator\BaseNameTrait;
use Zend\Code\Reflection\ClassReflection;

class ReflectionProvider implements EntityProviderInterface
{
    use BaseNameTrait;
    /**
     * @var string
     */
    private $entityClassName;
    private $relations = [
        ManyToOne::class,
        ManyToMany::class,
        OneToOne::class,
    ];
    /**
     * @var ValueProviderInterface
     */
    private $valueProvider;
    /**
     * @var array
     */
    private $spec = [];
    /**
     * @var array
     */
    private $params = [];
    /**
     * @var IdHandlerInterface
     */
    private $idHandler;
    /**
     * @var bool|null
     */
    private $hasUnique;
    /**
     * @var string|null
     */
    private $idName;

    public function __construct(
        string $entityClassName,
        ValueProviderInterface $valueProvider,
        IdHandlerInterface $idHandler
    ) {
        $this->entityClassName = $entityClassName;
        $this->valueProvider = $valueProvider;
        $this->idHandler = $idHandler;
    }

    /**
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function getEntitySpec(): array
    {
        if (empty($this->spec)) {
            $reflectionClassName = new ClassReflection($this->entityClassName);
            foreach ($reflectionClassName->getProperties() as $property) {
                foreach ($this->relations as $type) {
                    /** @var ManyToMany|OneToOne|ManyToOne $annotation */
                    $annotation = (new AnnotationReader())
                        ->getPropertyAnnotation($property, $type);
                    if ($annotation) {
                        $ref = new ClassReflection($annotation);
                        $columnType = strtolower($ref->getShortName());
                        $target = $annotation->targetEntity;
                        /** @var JoinColumn $joinColumn */
                        $joinColumn = (new AnnotationReader())
                            ->getPropertyAnnotation($property, JoinColumn::class);
                        if ($joinColumn) {
                            $nullable = $joinColumn->nullable;
                            $referenceColumn = $joinColumn->referencedColumnName;
                        }
                        break;
                    }
                }

                if (empty($columnType)) {
                    /** @var Column $column */
                    $column = (new AnnotationReader())
                        ->getPropertyAnnotation($property, Column::class);
                    $columnType = $column->type;
                }

                $this->spec[] = [
                    'name' => $property->getName(),
                    'type' => $columnType,
                    'entity' => $target ?? null,
                    'nullable' => $nullable ?? false,
                    'ref_column_key' => $referenceColumn ?? 'id',
                ];
            }
        }

        return $this->spec;
    }

    /**
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function getPostParams(): array
    {
        $params = $this->getParams();

        $postParams = [];

        $reflectionClassName = new ClassReflection($this->entityClassName);
        foreach ($reflectionClassName->getProperties() as $property) {
            $key = $property->getName();
            /** @var GeneratedValue $generated */
            $generated = (new AnnotationReader())
                ->getPropertyAnnotation($property, GeneratedValue::class);
            if (!empty($generated)) {
                continue;
            }
            $postParams[$key] = $params[$key];
        }

        return $postParams;
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getShortName(): string
    {
        return strtolower($this->getBaseName());
    }

    /**
     * Immutable
     * @return mixed
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function getId()
    {
        return $this->params[$this->getIdName()];
    }

    /**
     * Immutable
     * @return string
     */
    public function getEntityClassName(): string
    {
        return $this->entityClassName;
    }

    /**
     * Immutable
     * @return bool
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function hasUnique(): bool
    {
        if ($this->hasUnique === null) {
            $reflectionClassName = new ClassReflection($this->entityClassName);
            foreach ($reflectionClassName->getProperties() as $property) {
                /** @var Column $column */
                $column = (new AnnotationReader())
                    ->getPropertyAnnotation($property, Column::class);
                if ($column->unique) {
                    $this->hasUnique = true;
                    break;
                }
            }
        }

        return $this->hasUnique;
    }

    /**
     * Immutable
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function getParams(): array
    {
        if (empty($this->params)) {
            $reflectionClassName = new ClassReflection($this->entityClassName);
            foreach ($reflectionClassName->getProperties() as $property) {
                $key = $property->getName();
                /** @var GeneratedValue $generated */
                $generated = (new AnnotationReader())
                    ->getPropertyAnnotation($property, GeneratedValue::class);
                foreach ($this->relations as $type) {
                    /** @var ManyToMany|OneToOne|ManyToOne $annotation */
                    $annotation = (new AnnotationReader())
                        ->getPropertyAnnotation($property, $type);
                    if ($annotation) {
                        $target = $annotation->targetEntity;
                        $this->params[$key] = $this->idHandler->getId($target);
                        continue 2;
                    }
                }
                /** @var Column $column */
                $column = (new AnnotationReader())
                    ->getPropertyAnnotation($property, Column::class);
                $this->params[$key] = $this->valueProvider->getValue($column->type, !empty($generated));
            }
        }

        return $this->params;
    }

    /**
     * @return string
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function getIdName(): string
    {
        if ($this->idName === null) {
            $reflectionClassName = new ClassReflection($this->entityClassName);
            foreach ($reflectionClassName->getProperties() as $property) {
                /** @var Id $id */
                $id = (new AnnotationReader())
                    ->getPropertyAnnotation($property, Id::class);
                if (!empty($id)) {
                    $this->idName = $property->getName();
                    break;
                }
            }
        }

        return $this->idName;
    }
}
