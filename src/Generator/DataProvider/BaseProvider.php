<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\DataProvider;

use SlayerBirden\DFCodeGeneration\Util\Lexer;

final class BaseProvider implements DataProviderInterface
{
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
     */
    public function provide(): array
    {
        return [
            'entityName' => $this->entityClassName,
            'refName' => Lexer::getRefName($this->entityClassName),
            'entityClassName' => Lexer::getBaseName($this->entityClassName),
            'moduleName' => $this->getModuleName($this->entityClassName),
        ];
    }

    private function getModuleName(string $entityName): string
    {
        $parts = explode('\\', trim($entityName, '\\'));
        if (isset($parts[2])) {
            return $parts[2];
        }
        return end($parts);
    }
}
