<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Code;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpLiteral;
use Nette\PhpGenerator\PhpNamespace;

interface ArrayPartInterface
{
    /**
     * Add method for a part (required for SplitArrayCodeFeeder)
     *
     * @param string $key
     * @param array $data
     * @param ClassType $class
     * @param PhpNamespace $namespace
     */
    public function feed(string $key, array $data, ClassType $class, PhpNamespace $namespace): void;

    public function matches(string $key): bool;

    public function getCalleeCode(string $key, array $data, PhpNamespace $namespace): PhpLiteral;
}
