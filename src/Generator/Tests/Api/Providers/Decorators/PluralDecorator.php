<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests\Api\Providers\Decorators;

use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderDecoratorInterface;
use SlayerBirden\DFCodeGeneration\Generator\Tests\Api\EntitySpecProviderInterface;

final class PluralDecorator implements DataProviderDecoratorInterface
{
    /**
     * @var string
     */
    private $entityClassName;
    /**
     * @var EntityDataDecorator
     */
    private $entityDataDecorator;
    /**
     * @var EntitySpecProviderInterface
     */
    private $entitySpecProvider;
    /**
     * @var \Faker\Generator
     */
    private $generator;

    public function __construct(string $entityClassName, EntitySpecProviderInterface $entitySpecProvider)
    {
        $this->entityClassName = $entityClassName;
        $this->entityDataDecorator = new EntityDataDecorator($this->entityClassName, $entitySpecProvider);
        $this->entitySpecProvider = $entitySpecProvider;
        $this->generator = \Faker\Factory::create();
    }

    public function decorate(array $data): array
    {
        $data['entities'] = $this->generateEntities();
        $data['entitiesCount'] = count($data['entities']);
        $data['filterName'] = $this->getFilterName();
        $data['filterValue'] = $this->getFilterValue($data['entities'], $data['filterName']);
        $data['filteredEntities'] = $this->getFilteredEntities(
            $data['entities'],
            $data['filterName'],
            $data['filterValue']
        );
        $data['filterCount'] = count($data['filteredEntities']);
        $data['notFoundFilterValue'] = $this->getNotFoundFilterValue($data['entities'], $data['filterName']);
        $data['wrongFilterName'] = $this->getWrongFilterName();
        $data['wrongFilterValue'] = $this->getWrongFilterValue();
        $data['sortName'] = $this->getSortName();
        $data['sortDir'] = $this->getSortDir();
        $data['sorted'] = $this->getSortedEntities(
            $data['entities'],
            $data['sortName'],
            $data['sortDir']
        );

        return $data;
    }

    /**
     * @return array
     */
    private function generateEntities(): array
    {
        $entities = [];
        for ($i = 0; $i < 11; ++$i) {
            $entities[$i] = $this->entityDataDecorator->decorate([])['validEntityArray'];
        }

        return $entities;
    }

    private function getFilterName(): string
    {
        $spec = $this->entitySpecProvider->getSpec();
        $spec = array_filter($spec, function ($item) {
            return !$item['is_generated'];
        });

        $key = rand(0, count($spec) - 1);
        $currentKey = 0;

        reset($spec);
        while ($currentKey !== $key) {
            $currentKey += 1;
            next($spec);
        }

        return key($spec);
    }

    private function getFilterValue(array $entities, string $filterName): string
    {
        $key = rand(0, count($entities) - 1);

        $value = $entities[$key][$filterName];

        if (!is_numeric($value) && (strtotime($value) === false) && strlen($value) > 10) {
            return substr($value, 0, 5);
        }

        return $value;
    }

    private function getFilteredEntities(array $entities, string $filterName, string $filterValue): array
    {
        $filtered = [];

        foreach ($entities as $entity) {
            if (strpos($entity[$filterName], $filterValue) !== false) {
                $filtered[] = $entity;
            }
        }

        return $filtered;
    }

    private function getNotFoundFilterValue(array $entities, string $filterName): string
    {
        do {
            $value = $this->generator->text(30);
            $filtered = $this->getFilteredEntities($entities, $filterName, $value);
        } while (! empty($filtered));

        return $value;
    }

    private function getWrongFilterName(): string
    {
        $spec = $this->entitySpecProvider->getSpec();

        do {
            $key = $this->generator->word;
        } while (isset($spec[$key]));

        return $key;
    }

    private function getWrongFilterValue(): string
    {
        return $this->generator->text(20);
    }

    private function getSortName(): string
    {
        $spec = $this->entitySpecProvider->getSpec();

        $key = rand(0, count($spec) - 1);
        $currentKey = 0;

        reset($spec);
        while ($currentKey !== $key) {
            $currentKey += 1;
            next($spec);
        }

        return key($spec);
    }

    private function getSortDir(): string
    {
        if (rand(0, 1) === 0) {
            return 'ASC';
        }
        return 'DESC';
    }

    private function getSortedEntities(array $entities, string $sortName, string $sortDir): array
    {
        usort($entities, function (array $a, array $b) use ($sortName, $sortDir) {
            if ($sortDir === 'ASC') {
                return strcasecmp($a[$sortName], $b[$sortName]);
            } else {
                return strcasecmp($b[$sortName], $a[$sortName]);
            }
        });

        return array_slice($entities, 0, 10);
    }
}
