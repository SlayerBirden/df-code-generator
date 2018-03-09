<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers\Add;

use SlayerBirden\DFCodeGeneration\Generator\Controllers\NamingTrait;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class AbstractAction
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

    public function generate(): string
    {
        $loader = new FilesystemLoader(dirname(__DIR__) . '/Templates');
        $twig = new Environment($loader);

        return $twig->load($this->template)->render($this->getParams());
    }

    protected function getParams(): array
    {
        return [
            'ns' => $this->getNs($this->entityClassName),
            'useStatement' => ltrim($this->entityClassName, '\\'),
            'entityName' => $this->getBaseName($this->entityClassName),
        ];
    }
}
