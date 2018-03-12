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
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

class Add
{
    use NamingTrait;
    /**
     * @var string
     */
    private $entityClassName;
    private $ids = [];
    private $relations = [
        ManyToOne::class,
        ManyToMany::class,
        OneToOne::class,
    ];
    private $haveInRepoParams = [];
    private $unique = [];

    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }

    public function generate(): string
    {
        $className = 'Add' . $this->getBaseName($this->entityClassName) . 'Cest';
        $baseName = $this->getBaseName($this->entityClassName);
        $class = new ClassGenerator($className);

        $class->addMethodFromGenerator(
            (new MethodGenerator('_before'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getBefore())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('add' . $baseName))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getSuccessCase())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('addInvalid' . $baseName))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getValidationCase())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('addFailedConstraint' . $baseName))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getUniqueConstraintCase())
        );

        return (new FileGenerator())
            ->setClass($class)
            ->generate();
    }

    /**
     * @param string $entityClassName
     * @return string
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function getHaveInRepoPhrase(string $entityClassName): string
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
                    $body = $this->getRelationBody($body, $annotation->targetEntity, $key, $params);
                    continue;
                }
            }
            $params[$key] = $this->getColumnValue($property, $entityClassName, $isId);
        }
        $this->haveInRepoParams[$entityClassName] = $params;

        return sprintf($body, $entityClassName, var_export($params, true));
    }

    /**
     * @param \ReflectionProperty $property
     * @param string $entityClassName
     * @param bool $isId
     * @return \DateTime|int|string|null
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    private function getColumnValue(\ReflectionProperty $property, string $entityClassName, bool $isId)
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

    private function addUnique(string $entityClassName, string $column): void
    {
        if (isset($this->unique[$entityClassName])) {
            $this->unique[$entityClassName][] = $column;
        } else {
            $this->unique[$entityClassName] = [$column];
        }
    }

    private function getRelationBody(string $body, string $entityClassName, string $key, array &$params): string
    {
        $shortName = strtolower($this->getBaseName($entityClassName));
        $oldBody = $body;
        $body = $this->getHaveInRepoPhrase($entityClassName);
        $body .= PHP_EOL . $this->getUsagePhrase($shortName, $entityClassName, $this->getId($entityClassName));
        $body .= PHP_EOL . $oldBody;
        $params[$key] = '$' . $shortName;

        return $body;
    }

    private function getIncrId(string $entityClassName): int
    {
        if (isset($this->ids[$entityClassName])) {
            return $this->ids[$entityClassName]++;
        } else {
            return $this->ids[$entityClassName] = 1;
        }
    }

    private function getId(string $entityClassName): ?int
    {
        if (isset($this->ids[$entityClassName])) {
            return $this->ids[$entityClassName];
        }
        return null;
    }

    private function getUsagePhrase(string $shortName, string $entityClassName, int $id): string
    {
        $body = '$%s = $I->grabEntityFromRepository(%s, [\'id\' => %d]);';

        return sprintf($body, $shortName, $entityClassName, $id);
    }

    /**
     * @return string
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function getBefore(): string
    {
        return $this->getHaveInRepoPhrase($this->entityClassName);
    }

    private function getSuccessCase(): string
    {
        $body = <<<'BODY'
$I->wantTo('create %1$s');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/%1$s', %2$s);
$I->seeResponseCodeIs(HttpCode::OK);
$I->seeResponseContainsJson([
    'success' => true,
    'data' => [
        '%1$s' => %2$s
    ]
]);
BODY;

        $shortName = strtolower($this->getBaseName($this->entityClassName));

        return sprintf($body, $shortName, var_export($this->getCreateParams(), true));
    }

    private function getCreateParams(): array
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

    private function getValidationCase(): string
    {
        $shortName = strtolower($this->getBaseName($this->entityClassName));
        $params = $this->getCreateParams();
        if (count($params) > 0) {
            $body = <<<'BODY'
$I->wantTo('create incomplete %1$s');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/%1$s', %2$s);
$I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
$I->seeResponseContainsJson([
    'success' => false,
    'data' => [
        'validation' => %3$s
    ]
]);
BODY;

            $validation = [];
            foreach ($params as $key => $val) {
                $validation[] = [
                    'field' => $key
                ];
                unset($params[$key]);
                break;
            }
            return sprintf($body, $shortName, var_export($params, true), var_export($validation, true));
        } else {
            return '//TODO add validation case';
        }
    }

    private function getUniqueConstraintCase(): string
    {
        if (empty($this->unique)) {
            return '//TODO add unique case';
        }

        $shortName = strtolower($this->getBaseName($this->entityClassName));
        $params = $this->haveInRepoParams[$this->entityClassName];
        foreach ($params as $key => $param) {
            if (is_object($param)) {
                $params[$key] = $param->getId();
            }
        }

        $body = <<<'BODY'
$I->wantTo('create %1$s with failed constraint');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/%1$s', %2$s);
$I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
$I->seeResponseContainsJson([
    'success' => false,
]);
BODY;

        return sprintf($body, $shortName, var_export($params, true));
    }
}
