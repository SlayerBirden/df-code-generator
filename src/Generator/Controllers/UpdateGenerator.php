<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\Parameter;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Property;
use Nette\PhpGenerator\PsrPrinter;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderInterface;
use SlayerBirden\DFCodeGeneration\Generator\GeneratorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class UpdateGenerator implements GeneratorInterface
{
    /**
     * @var DataProviderInterface
     */
    private $dataProvider;
    /**
     * @var Environment
     */
    private $twig;

    public function __construct(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $loader = new FilesystemLoader(__DIR__ . '/Templates');
        $this->twig = new Environment($loader);
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function getProcessMethodBody(): string
    {
        return $this->twig->load('Update/Process.template.twig')->render($this->dataProvider->provide());
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function generate(): string
    {
        $fullEntityName = $this->getEntityName();
        $file = new PhpFile;
        $file->addComment('This file is generated by SlayerBirden\DFCodeGeneration');
        $file->setStrictTypes();

        $namespace = $file->addNamespace($this->getNamespace());
        $this->addUsages($namespace);

        $class = $namespace->addClass($this->getShortClassName());
        $class->setFinal()
            ->setImplements(['Psr\Http\Server\MiddlewareInterface'])
            ->setProperties([
                (new Property('managerRegistry'))->setVisibility(ClassType::VISIBILITY_PRIVATE)
                    ->addComment('@var EntityManagerRegistry'),
                (new Property('hydrator'))->setVisibility(ClassType::VISIBILITY_PRIVATE)
                    ->addComment('@var HydratorInterface'),
                (new Property('inputFilter'))->setVisibility(ClassType::VISIBILITY_PRIVATE)
                    ->addComment('@var InputFilterInterface'),
                (new Property('logger'))->setVisibility(ClassType::VISIBILITY_PRIVATE)
                    ->addComment('@var LoggerInterface'),
            ])
            ->setMethods([
                (new Method('__construct'))->setParameters([
                    (new Parameter('managerRegistry'))
                        ->setTypeHint('SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry'),
                    (new Parameter('hydrator'))
                        ->setTypeHint('Zend\Hydrator\HydratorInterface'),
                    (new Parameter('inputFilter'))
                        ->setTypeHint('Zend\InputFilter\InputFilterInterface'),
                    (new Parameter('logger'))
                        ->setTypeHint('Psr\Log\LoggerInterface'),
                ])->setBody(<<<'BODY'
$this->managerRegistry = $managerRegistry;
$this->hydrator = $hydrator;
$this->inputFilter = $inputFilter;
$this->logger = $logger;
BODY
                )->setVisibility(ClassType::VISIBILITY_PUBLIC),
                (new Method('process'))
                    ->setParameters([
                        (new Parameter('request'))
                            ->setTypeHint('Psr\Http\Message\ServerRequestInterface'),
                        (new Parameter('handler'))
                            ->setTypeHint('Psr\Http\Server\RequestHandlerInterface'),
                    ])
                    ->setReturnType('Psr\Http\Message\ResponseInterface')
                    ->setComment("@inheritdoc\n")
                    ->setBody($this->getProcessMethodBody())
                    ->setVisibility(ClassType::VISIBILITY_PUBLIC),
            ]);

        return (new PsrPrinter())->printFile($file);
    }

    private function addUsages(PhpNamespace $namespace)
    {
        if ($this->hasUnique()) {
            $namespace->addUse('Doctrine\DBAL\Exception\UniqueConstraintViolationException');
        }
        $namespace->addUse('Doctrine\ORM\ORMException');
        $namespace->addUse('Doctrine\ORM\ORMInvalidArgumentException');
        $namespace->addUse('Psr\Http\Message\ResponseInterface');
        $namespace->addUse('Psr\Http\Message\ServerRequestInterface');
        $namespace->addUse('Psr\Http\Server\MiddlewareInterface');
        $namespace->addUse('Psr\Http\Server\RequestHandlerInterface');
        $namespace->addUse('Psr\Log\LoggerInterface');
        $namespace->addUse('SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface');
        $namespace->addUse('SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry');
        $namespace->addUse($this->getEntityName());
        $namespace->addUse('SlayerBirden\DataFlowServer\Stdlib\Request\Parser');
        $namespace->addUse('SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralErrorResponseFactory');
        $namespace->addUse('SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralSuccessResponseFactory');
        $namespace->addUse('SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory');
        $namespace->addUse('Zend\Hydrator\HydratorInterface');
        $namespace->addUse('Zend\InputFilter\InputFilterInterface');
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->getFullClassName();
    }

    /**
     * @return string
     */
    private function getShortClassName(): string
    {
        return 'Update' . $this->dataProvider->provide()['entityClassName'] . 'Action';
    }

    /**
     * @return string
     */
    private function getFullClassName(): string
    {
        return $this->getNamespace() . '\\' . $this->getShortClassName();
    }

    private function getEntityName(): string
    {
        return $this->dataProvider->provide()['entityName'];
    }

    private function hasUnique(): bool
    {
        return (bool)$this->dataProvider->provide()['hasUnique'];
    }

    private function getNamespace(): string
    {
        return $this->dataProvider->provide()['controller_namespace'];
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return sprintf(
            'src/%s/Controller/%s.php',
            $this->dataProvider->provide()['moduleName'],
            $this->getShortClassName()
        );
    }
}
