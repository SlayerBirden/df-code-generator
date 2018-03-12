<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Faker\Factory;
use SlayerBirden\DFCodeGeneration\Generator\NamingTrait;

abstract class AbstractTest
{
    use NamingTrait;
    /**
     * @var string
     */
    protected $entityClassName;
    protected $ids = [];
    protected $relations = [
        ManyToOne::class,
        ManyToMany::class,
        OneToOne::class,
    ];
    protected $haveInRepoParams = [];
    protected $unique = [];
    protected $existingRelations = [];
    /**
     * @var string
     */
    protected $shortName;

    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;
        $this->shortName = strtolower($this->getBaseName($this->entityClassName));
    }

    abstract public function generate(): string;

    /**
     * @param int $count
     * @return string
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    protected function getBefore(int $count = 1): string
    {
        $parts = [];
        for ($i = 0; $i < $count; ++$i) {
            $parts[] = $this->getHaveInRepoPhrase($this->entityClassName);
        }

        return implode(PHP_EOL, $parts);
    }

    /**
     * @param string $entityClassName
     * @return string
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    protected function getHaveInRepoPhrase(string $entityClassName): string
    {
        $body = '$I->haveInRepository(%s, %s);';

        $params = [];
        $reflectionClassName = new \ReflectionClass($entityClassName);
        foreach ($reflectionClassName->getProperties() as $property) {
            $isId = false;
            $key = $property->getName();
            /** @var Id $id */
            $id = (new AnnotationReader())
                ->getPropertyAnnotation($property, Id::class);
            if ($id) {
                $isId = true;
            }
            foreach ($this->relations as $type) {
                $annotation = (new AnnotationReader())
                    ->getPropertyAnnotation($property, $type);
                if ($annotation) {
                    $body = $this->getRelationBody($body, $annotation->targetEntity, $key, $params, $type);
                    continue 2;
                }
            }
            $params[$key] = $this->getColumnValue($property, $entityClassName, $isId);
        }
        $this->haveInRepoParams[$entityClassName][] = $params;

        return sprintf($body, $entityClassName, var_export($params, true));
    }

    protected function getHaveInRepoParams(string $entityClassName, int $idx = 0): array
    {
        if (isset($this->haveInRepoParams[$entityClassName]) && isset($this->haveInRepoParams[$entityClassName][$idx])) {
            return $this->haveInRepoParams[$entityClassName][$idx];
        }
        return [];
    }

    /**
     * @param \ReflectionProperty $property
     * @param string $entityClassName
     * @param bool $isId
     * @return \DateTime|int|string|null
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    protected function getColumnValue(\ReflectionProperty $property, string $entityClassName, bool $isId)
    {
        $faker = Factory::create();
        /** @var Column $column */
        $column = (new AnnotationReader())
            ->getPropertyAnnotation($property, Column::class);
        $value = null;
        if (!$column->nullable) {
            switch ($column->type) {
                case 'string':
                    $value = $faker->word;
                    break;
                case 'integer':
                    if ($isId) {
                        $value = $this->getIncrId($entityClassName);
                    } else {
                        $value = $faker->numberBetween(1, 10);
                    }
                    break;
                case 'datetime':
                    $value = $faker->dateTime();
                    break;
            }
        }
        if ($column->unique) {
            $this->addUnique($entityClassName, $property->getName());
        }
        return $value;
    }

    protected function addUnique(string $entityClassName, string $column): void
    {
        if (isset($this->unique[$entityClassName])) {
            $this->unique[$entityClassName][] = $column;
        } else {
            $this->unique[$entityClassName] = [$column];
        }
    }

    /**
     * @param string $body
     * @param string $entityClassName
     * @param string $key
     * @param array $params
     * @param string $type
     * @return string
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    protected function getRelationBody(
        string $body,
        string $entityClassName,
        string $key,
        array &$params,
        string $type
    ): string {
        $shortName = strtolower($this->getBaseName($entityClassName));
        if (!in_array($shortName, $this->existingRelations, true) || $type !== OneToOne::class) {
            $oldBody = $body;
            $body = $this->getHaveInRepoPhrase($entityClassName);
            $body .= PHP_EOL . $this->getUsagePhrase($shortName, $entityClassName, $this->getId($entityClassName));
            $body .= PHP_EOL . $oldBody;
            $this->existingRelations[] = $shortName;
        }
        $params[$key] = '$' . $shortName;

        return $body;
    }

    protected function getIncrId(string $entityClassName): int
    {
        if (isset($this->ids[$entityClassName])) {
            return ++$this->ids[$entityClassName];
        } else {
            return $this->ids[$entityClassName] = 1;
        }
    }

    protected function getId(string $entityClassName): ?int
    {
        if (isset($this->ids[$entityClassName])) {
            return $this->ids[$entityClassName];
        }
        return null;
    }

    protected function getUsagePhrase(string $shortName, string $entityClassName, int $id): string
    {
        $body = '$%s = $I->grabEntityFromRepository(%s, [\'id\' => %d]);';

        return sprintf($body, $shortName, $entityClassName, $id);
    }

    /**
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    protected function getPostParams(): array
    {
        $params = [];
        $reflectionClassName = new \ReflectionClass($this->entityClassName);
        foreach ($reflectionClassName->getProperties() as $property) {
            $key = $property->getName();
            /** @var Id $id */
            $id = (new AnnotationReader())
                ->getPropertyAnnotation($property, Id::class);
            if ($id) {
                continue;
            }
            foreach ($this->relations as $type) {
                $annotation = (new AnnotationReader())
                    ->getPropertyAnnotation($property, $type);
                if ($annotation) {
                    $params[$key] = $this->getId($annotation->targetEntity);
                    continue;
                }
            }
            $params[$key] = $this->getColumnValue($property, $this->entityClassName, false);
        }

        return $params;
    }
}
