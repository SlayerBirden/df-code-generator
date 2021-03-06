<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Code\Parts;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpLiteral;
use Nette\PhpGenerator\PhpNamespace;
use SlayerBirden\DFCodeGeneration\Code\Printer\NsArrayPrinter;
use SlayerBirden\DFCodeGeneration\Generator\Config\Code\ArrayPartInterface;
use Zend\Filter\Word\UnderscoreToCamelCase;

final class DefaultCodeFeederPart implements ArrayPartInterface
{
    public function feed(string $key, array $data, ClassType $class, PhpNamespace $namespace): void
    {
        $class->addMethod($this->getMethodName($key))
            ->setVisibility(ClassType::VISIBILITY_PRIVATE)
            ->setReturnType('array')
            ->setBody(
                sprintf('return %s;', (new NsArrayPrinter($namespace))->printArray($data, 1, ''))
            );
    }

    private function getMethodName(string $key): string
    {
        if (strpos($key, '\\')) {
            //get the last part
            $parts = explode('\\', $key);
            $key = end($parts);
        }
        return 'get' . (new UnderscoreToCamelCase())->filter($key) . 'Config';
    }

    public function matches(string $key): bool
    {
        return true;
    }

    public function getCalleeCode(string $key, array $data, PhpNamespace $namespace): PhpLiteral
    {
        return new PhpLiteral(sprintf('$this->%s()', $this->getMethodName($key)));
    }
}
