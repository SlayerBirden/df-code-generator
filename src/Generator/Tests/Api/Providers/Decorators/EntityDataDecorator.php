<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests\Api\Providers\Decorators;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
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
            $generated = (new AnnotationReader())
                ->getPropertyAnnotation($property, GeneratedValue::class);
            if ($generated) {
                continue;
            }
            /** @var Column $annotation */
            $annotation = (new AnnotationReader())
                ->getPropertyAnnotation($property, Column::class);
            if (!$annotation) {
                continue;
            }
            $spec[$property->getName()] = [
                'required' => !$annotation->nullable,
                'type' => $annotation->type,
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
            $columns[$code] = $this->getDataByType($definition['type']);
        }

        return $columns;
    }

    private function getDataByType(string $type): string
    {
        switch ($type) {
            case 'datetime':
                return $this->generator->date(DATE_RFC3339);
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
            if ($definition['required']) {
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
            switch ($definition['type']) {
                case 'string':
                    $columns[$code] = $this->generator->words;
                    break;
                case 'datetime':
                    $columns[$code] = $this->generator->word;
                    break;
                default:
                    $columns[$code] = '';
                    break;
            }
        }

        return $columns;
    }
}
