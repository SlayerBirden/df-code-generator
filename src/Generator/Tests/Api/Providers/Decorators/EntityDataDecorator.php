<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests\Api\Providers\Decorators;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Faker\Generator;
use Faker\Provider\DateTime;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderDecoratorInterface;

final class EntityDataDecorator implements DataProviderDecoratorInterface
{
    /**
     * @var string
     */
    private $entityClassName;
    /**
     * @var Generator
     */
    private $generator;

    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;
        $this->generator = \Faker\Factory::create();
        $this->generator->addProvider(new DateTime($this->generator));
    }

    /**
     * @param array $data
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function decorate(array $data): array
    {
        $data['validEntityArray'] = $this->getAllColumns();
        $data['incompleteEntityArray'] = $this->getIncompleteColumns();
        $data['invalidEntityArray'] = $this->getWrongDataColumns();

        return $data;
    }

    /**
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function getSpec(): array
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
                'is_generated' => $generated !== null,
            ];
        }
        return $spec;
    }

    /**
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function getAllColumns(): array
    {
        $columns = [];
        foreach ($this->getSpec() as $code => $definition) {
            if ($definition['is_generated']) {
                continue;
            }
            $columns[$code] = $this->getDataByType($definition['type']);
        }

        return $columns;
    }

    /**
     * @param string $type
     * @return int|string
     */
    private function getDataByType(string $type)
    {
        switch ($type) {
            case 'datetime':
                return $this->generator->date(DATE_RFC3339);
            case 'integer':
                return $this->generator->numberBetween(1, 10);
            default:
                return $this->generator->word;
        }
    }

    /**
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function getIncompleteColumns(): array
    {
        $columns = [];
        foreach ($this->getSpec() as $code => $definition) {
            if ($definition['is_generated'] || $definition['required']) {
                continue;
            } else {
                $columns[$code] = $this->getDataByType($definition['type']);
            }
        }

        return $columns;
    }

    /**
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function getWrongDataColumns(): array
    {
        $columns = [];
        foreach ($this->getSpec() as $code => $definition) {
            if ($definition['is_generated']) {
                continue;
            }
            switch ($definition['type']) {
                case 'string':
                    $columns[$code] = $this->generator->words;
                    break;
                case 'datetime':
                    $columns[$code] = $this->generator->word;
                    break;
                case 'integer':
                    $columns[$code] = $this->generator->numberBetween(0, 100);
                    break;
                default:
                    $columns[$code] = '';
                    break;
            }
        }

        return $columns;
    }
}
