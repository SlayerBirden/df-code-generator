<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator;

use Zend\Code\Reflection\ClassReflection;

trait BaseNameTrait
{
    /**
     * @var string
     */
    private $baseName;

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getBaseName(): string
    {
        if ($this->baseName === null) {
            $reflection = new ClassReflection($this->entityClassName);
            $this->baseName = $reflection->getShortName();
        }

        return $this->baseName;

    }
}
