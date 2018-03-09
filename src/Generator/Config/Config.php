<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use SlayerBirden\DFCodeGeneration\Generator\Factory\NamingTrait as FactoryNamingTrait;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\NamingTrait as ControllerNamingTrait;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;

class Config
{
    use FactoryNamingTrait, ControllerNamingTrait {
        FactoryNamingTrait::getBaseName insteadof ControllerNamingTrait;
        FactoryNamingTrait::getNs insteadof ControllerNamingTrait;
        FactoryNamingTrait::getNs as getFactoryNs;
        ControllerNamingTrait::getNs as getControllerNs;
    }

    /**
     * @var string
     */
    private $entityClassName;

    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }

    public function generate(): string
    {
        $config = $this->getConfigObject();

        $new = [
            '\\Zend\\ServiceManager\\AbstractFactory\\ConfigAbstractFactory' => $this->getAbstractFactoryConfig(),
            'doctrine' => [
                'paths' => [
                    $this->getEntitiesSrc(),
                ],
            ],
            'dependencies' => [
                'delegators' => [
                    '\\Zend\\Expressive\\Application' => [
                        $this->getFactoryNs($this->entityClassName) . '\\RoutesDelegator',
                    ]
                ]
            ],
            'input_filter_specs' => $this->getInputFilterConfig(),
        ];

        if ($config) {
            $existing = call_user_func($config);
            $body = var_export(array_replace_recursive($existing, $new), true);

            $reflection = new \ReflectionClass($config);
            $generator = ClassGenerator::fromReflection($reflection);
            $generator->getMethod('__invoke')->setBody($body);

            return (new FileGenerator())
                ->setClass($generator)
                ->generate();
        }

        return (new FileGenerator())
            ->setNamespace($this->getNs())
            ->setClass(
                (new ClassGenerator('ConfigProvider'))
                    ->addMethodFromGenerator(
                        (new MethodGenerator('__invoke'))
                            ->setBody(var_export($new, true))
                    )
            )
            ->generate();
    }

    private function getNs()
    {
        $parts = explode('\\', $this->entityClassName);
        array_splice($parts, -2); // Entities\Model

        return ltrim(implode('\\', $parts), '\\');
    }

    private function getConfigObject()
    {
        $config = $this->getNs() . '\\' . 'ConfigProvider';

        if (class_exists($config)) {
            return new $config();
        }

        return null;
    }

    private function getController(string $type): string
    {
        $baseName = $this->getBaseName($this->entityClassName);
        if ($type === 'gets') {
            $baseName .= 's';
            $type = 'get';
        }

        return $this->getControllerNs($this->entityClassName) . '\\' . ucwords($type) . $baseName . 'Action';
    }

    private function getAbstractFactoryConfig(): array
    {
        $baseName = $this->getBaseName($this->entityClassName);
        return [
            $this->getController('add') => [
                EntityManagerInterface::class,
                '\\Zend\\Hydrator\\ClassMethods',
                $baseName . 'InputFilter',
                '\\Psr\\Log\\LoggerInterface',
                '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
            ],
            $this->getController('update') => [
                EntityManagerInterface::class,
                '\\Zend\\Hydrator\\ClassMethods',
                $baseName . 'InputFilter',
                '\\Psr\\Log\\LoggerInterface',
                '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
            ],
            $this->getController('get') => [
                EntityManagerInterface::class,
                '\\Psr\\Log\\LoggerInterface',
                '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
            ],
            $this->getController('gets') => [
                EntityManagerInterface::class,
                '\\Psr\\Log\\LoggerInterface',
                '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
            ],
            $this->getController('delete') => [
                EntityManagerInterface::class,
                '\\Psr\\Log\\LoggerInterface',
                '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
            ],
        ];
    }

    private function getInputFilterConfig(): array
    {
        $inputFilter = [];

        $reflectionClassName = new \ReflectionClass($this->entityClassName);
        foreach ($reflectionClassName->getProperties() as $property) {
            $id = (new AnnotationReader())
                ->getPropertyAnnotation($property, \Doctrine\ORM\Mapping\Id::class);
            if ($id) {
                continue;
            }
            /** @var \Doctrine\ORM\Mapping\Column $annotation */
            $annotation = (new AnnotationReader())
                ->getPropertyAnnotation($property, \Doctrine\ORM\Mapping\Column::class);
            $inputFilter[$property->getName()] = [
                'required' => !$annotation->nullable,
                'filters' => [
                    [
                        'name' => 'stringtrim',
                    ]
                ],
            ];
            if (!$annotation->nullable) {
                $inputFilter[$property->getName()]['validators'] = [
                    [
                        'name' => 'notempty',
                    ],
                ];
            }
        }

        return $inputFilter;
    }

    private function getEntitiesSrc(): string
    {
        // expect to have 3d part as Module
        $parts = explode('\\', $this->entityClassName);
        if (isset($parts[2])) {
            return sprintf('src/%s/Entities', $parts[2]);
        }

        return '';
    }
}
