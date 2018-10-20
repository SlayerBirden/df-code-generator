<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests\Api\Providers\Decorators;

use Faker\Generator;
use Faker\Provider\DateTime;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderDecoratorInterface;
use SlayerBirden\DFCodeGeneration\Generator\Tests\Api\EntitySpecProviderInterface;

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
    /**
     * @var EntitySpecProviderInterface
     */
    private $entitySpecProvider;

    public function __construct(string $entityClassName, EntitySpecProviderInterface $entitySpecProvider)
    {
        $this->entityClassName = $entityClassName;
        $this->generator = \Faker\Factory::create();
        $this->generator->addProvider(new DateTime($this->generator));
        $this->entitySpecProvider = $entitySpecProvider;
    }

    /**
     * @param array $data
     * @return array
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
     */
    private function getAllColumns(): array
    {
        $columns = [];
        foreach ($this->entitySpecProvider->getSpec() as $code => $definition) {
            if ($definition['is_generated']) {
                continue;
            }
            $columns[$code] = $this->getDataByType($definition['type'], $definition['is_unique']);
        }

        return $columns;
    }

    /**
     * @param string $type
     * @param bool $unique
     * @return int|string
     */
    private function getDataByType(string $type, bool $unique = false)
    {
        $generator = $unique ? $this->generator : $this->generator->unique();
        switch ($type) {
            case 'datetime':
                return $generator->date(DATE_RFC3339);
            case 'integer':
                return $generator->numberBetween(1, 100);
            default:
                return rand(0, 5) > 3 ? $generator->text(70) : $generator->word;
        }
    }

    /**
     * @return array
     */
    private function getIncompleteColumns(): array
    {
        $columns = [];
        foreach ($this->entitySpecProvider->getSpec() as $code => $definition) {
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
     */
    private function getWrongDataColumns(): array
    {
        $columns = [];
        foreach ($this->entitySpecProvider->getSpec() as $code => $definition) {
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
