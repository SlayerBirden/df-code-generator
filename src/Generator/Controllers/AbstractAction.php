<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers;

use SlayerBirden\DFCodeGeneration\Generator\GeneratorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class AbstractAction implements GeneratorInterface
{
    /**
     * @var string
     */
    protected $template = '';
    /**
     * @var DataProviderInterface
     */
    protected $provider;

    public function __construct(DataProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function generate(): string
    {
        $loader = new FilesystemLoader(__DIR__ . '/Templates');
        $twig = new Environment($loader);

        return $twig->load($this->template)->render($this->provider->provide());
    }
}
