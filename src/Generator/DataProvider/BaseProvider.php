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
     * @throws \ReflectionException
     */
    public function provide(): array
    {
        return [
            'entityName' => $this->entityClassName,
            'refName' => Lexer::getRefName($this->entityClassName),
            'entityClassName' => Lexer::getBaseName($this->entityClassName),
        ];
    }
}
