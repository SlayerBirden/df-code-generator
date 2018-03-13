<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers\Add;

use SlayerBirden\DFCodeGeneration\Generator\Controllers\NamingTrait;
use SlayerBirden\DFCodeGeneration\Generator\GeneratorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class AbstractAction implements GeneratorInterface
{
    use NamingTrait;
    /**
     * @var string
     */
    protected $template = '';
    /**
     * @var string
     */
    protected $entityClassName;

    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }

    /**
     * @return string
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function generate(): string
    {
        $loader = new FilesystemLoader(dirname(__DIR__) . '/Templates');
        $twig = new Environment($loader);

        return $twig->load($this->template)->render($this->getParams());
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    protected function getParams(): array
    {
        return [
            'ns' => $this->getNs($this->entityClassName),
            'useStatement' => ltrim($this->entityClassName, '\\'),
            'entityName' => $this->getBaseName($this->entityClassName),
        ];
    }
}
