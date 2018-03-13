<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator;

use Zend\Code\Reflection\ClassReflection;

trait NamingTrait
{
    /**
     * @param string $entityClassName
     * @return string
     * @throws \ReflectionException
     */
    protected function getBaseName(string $entityClassName): string
    {
        $reflection = new ClassReflection($entityClassName);
        return $reflection->getShortName();
    }
}
