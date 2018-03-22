<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Factory;

use SlayerBirden\DFCodeGeneration\Generator\BaseNameTrait;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\SimpleProvider as ControllerDataProvider;
use SlayerBirden\DFCodeGeneration\Util\Lexer;

class SimpleProvider implements DataProviderInterface
{
    use BaseNameTrait;

    /**
     * @var string
     */
    private $entityClassName;

    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function provide(): array
    {
        $controllerParams = (new ControllerDataProvider($this->entityClassName))->provide();

        return [
            'ns' => $this->getNs(),
            'controllerNs' => $controllerParams['ns'],
            'entityName' => $this->getBaseName(),
            'pluralEntityName' => Lexer::getPluralForm($this->getBaseName()),
        ];
    }

    private function getNs(): string
    {
        $parts = explode('\\', $this->entityClassName);
        array_splice($parts, -2); // Entities\Model

        $parts[] = 'Factory';

        return ltrim(implode('\\', $parts), '\\');
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getClassName(): string
    {
        return $this->getNs() . '\\' . $this->getBaseName() . 'RoutesDelegator';
    }
}
