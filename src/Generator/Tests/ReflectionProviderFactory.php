<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

class ReflectionProviderFactory implements EntityProviderFactoryInterface
{
    /**
     * @var IdHandlerInterface
     */
    private $idHandler;

    public function __construct(IdHandlerInterface $idHandler)
    {
        $this->idHandler = $idHandler;
    }

    public function create(string $entityClassName): EntityProviderInterface
    {
        return new ReflectionProvider(
            $entityClassName,
            new FakerValueProvider($entityClassName, $this->idHandler),
            $this->idHandler
        );
    }
}
