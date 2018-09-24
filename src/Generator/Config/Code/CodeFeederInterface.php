<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Code;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

interface CodeFeederInterface
{
    public function feed(array $data, ClassType $class, PhpNamespace $namespace): void;
}
