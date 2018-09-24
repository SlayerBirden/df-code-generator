<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Code;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpLiteral;
use Nette\PhpGenerator\PhpNamespace;
use SlayerBirden\DFCodeGeneration\Code\Printer\NsArrayPrinter;

final class SplitArrayCodeFeeder implements CodeFeederInterface
{
    /**
     * @var ArrayPartInterface[]
     */
    private $parts;

    public function __construct(ArrayPartInterface ...$parts)
    {
        $this->parts = $parts;
    }

    public function feed(array $data, ClassType $class, PhpNamespace $namespace): void
    {
        $invoke = [];

        foreach ($data as $key => $config) {
            foreach ($this->parts as $part) {
                if ($part->matches($key)) {
                    $invoke[$key] = $part->getCalleeCode($key, $config, $namespace);
                    break 1;
                }
            }
        }

        $invokeBody = 'return ' . (new NsArrayPrinter($namespace))->printArray($invoke, 1, '') . ";\n";
        $class->addMethod('__invoke')
            ->setVisibility(ClassType::VISIBILITY_PUBLIC)
            ->setReturnType('array')
            ->setBody($invokeBody);

        foreach ($data as $key => $config) {
            foreach ($this->parts as $part) {
                if ($part->matches($key)) {
                    $part->feed($key, $config, $class, $namespace);
                    break 1;
                }
            }
        }
    }
}
