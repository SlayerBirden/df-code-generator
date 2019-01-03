<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests\Api;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Parameter;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderInterface;
use SlayerBirden\DFCodeGeneration\Generator\GeneratorInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
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
        $filter = new \Twig\TwigFilter('underscore', function ($string) {
            return (new CamelCaseToSnakeCaseNameConverter())->normalize($string);
        });
        $this->twig->addFilter($filter);
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function generate(): string
    {
        $file = new PhpFile();
        $file->setStrictTypes();
        $file->addComment('This file is generated by SlayerBirden\DFCodeGeneration');

        $namespace = $file->addNamespace($this->getNameSpace());

        $namespace->addUse('codecept\ApiTester');
        $namespace->addUse('Codeception\Util\HttpCode');
        $namespace->addUse($this->dataProvider->provide()['entityName']);
        if ($this->dataProvider->provide()['has_owner']) {
            $namespace->addUse('SlayerBirden\DataFlowServer\Domain\Entities\User');
        }

        $class = $namespace->addClass('UpdateCest');

        $class->addMethod('_before')
            ->setParameters([
                (new Parameter('I'))->setTypeHint('\codecept\ApiTester'),
            ])
            ->setBody(
                $this->twig->load('Update/before.template.twig')->render($this->dataProvider->provide())
            )
            ->setReturnType('void')
            ->setVisibility(ClassType::VISIBILITY_PUBLIC);

        $class->addMethod('update' . $this->dataProvider->provide()['entityClassName'])
            ->setParameters([
                (new Parameter('I'))->setTypeHint('\codecept\ApiTester'),
            ])
            ->setBody(
                $this->twig->load('Update/update.template.twig')->render($this->dataProvider->provide())
            )
            ->setReturnType('void')
            ->setVisibility(ClassType::VISIBILITY_PUBLIC);

        $class->addMethod('updateNonExisting' . $this->dataProvider->provide()['entityClassName'])
            ->setParameters([
                (new Parameter('I'))->setTypeHint('\codecept\ApiTester'),
            ])
            ->setBody(
                $this->twig->load('Update/update.nonexisting.template.twig')->render($this->dataProvider->provide())
            )
            ->setReturnType('void')
            ->setVisibility(ClassType::VISIBILITY_PUBLIC);

        $class->addMethod('updatePartial' . $this->dataProvider->provide()['entityClassName'])
            ->setParameters([
                (new Parameter('I'))->setTypeHint('\codecept\ApiTester'),
            ])
            ->setBody(
                $this->twig->load('Update/update.partial.template.twig')->render($this->dataProvider->provide())
            )
            ->setReturnType('void')
            ->setVisibility(ClassType::VISIBILITY_PUBLIC);

        return (new PsrPrinter())->printFile($file);
    }

    public function getClassName(): string
    {
        return $this->getNameSpace() . '\\' . 'UpdateCest';
    }

    public function getFileName(): string
    {
        return sprintf(
            'tests/api/%s/%s/UpdateCest.php',
            strtolower($this->dataProvider->provide()['moduleName']),
            $this->dataProvider->provide()['refName']
        );
    }

    private function getNameSpace(): string
    {
        return sprintf(
            'codecept\%s\%s',
            strtolower($this->dataProvider->provide()['moduleName']),
            $this->dataProvider->provide()['refName']
        );
    }
}
