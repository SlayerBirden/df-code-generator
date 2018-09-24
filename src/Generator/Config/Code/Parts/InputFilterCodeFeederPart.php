<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Code\Parts;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpLiteral;
use Nette\PhpGenerator\PhpNamespace;
use SlayerBirden\DFCodeGeneration\Code\Printer\NsArrayPrinter;
use SlayerBirden\DFCodeGeneration\Generator\Config\Code\ArrayPartInterface;

final class InputFilterCodeFeederPart implements ArrayPartInterface
{
    /**
     * @var ArrayPartInterface[]
     */
    private $parts;

    public function __construct(ArrayPartInterface ...$parts)
    {
        $this->parts = $parts;
    }

    /**
     * Add method for a part (required for SplitArrayCodeFeeder)
     *
     * @param string $key
     * @param array $data
     * @param ClassType $class
     * @param PhpNamespace $namespace
     */
    public function feed(string $key, array $data, ClassType $class, PhpNamespace $namespace): void
    {
        foreach ($data as $dataKey => $dataValue) {
            foreach ($this->parts as $part) {
                if ($part->matches($dataKey)) {
                    $part->feed($dataKey, $dataValue, $class, $namespace);
                    break 1;
                }
            }
        }
    }

    public function matches(string $key): bool
    {
        return $key === 'input_filter_specs';
    }

    public function getCalleeCode(string $key, array $data, PhpNamespace $namespace): PhpLiteral
    {
        $localBody = [];
        foreach ($data as $dataKey => $dataValue) {
            foreach ($this->parts as $part) {
                if ($part->matches($dataKey)) {
                    $localBody[$dataKey] = $part->getCalleeCode($dataKey, $dataValue, $namespace);
                    break 1;
                }
            }
        }

        return new PhpLiteral(
            (new NsArrayPrinter($namespace))->printArray($localBody, 2, "", null, false)
        );
    }
}
