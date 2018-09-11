<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Code\Printer;

use Nette\PhpGenerator\PhpLiteral;
use Nette\PhpGenerator\PhpNamespace;
use SlayerBirden\DFCodeGeneration\Util\ArrayUtils;

final class NsArrayPrinter
{
    /**
     * @var PhpNamespace
     */
    private $phpNamespace;

    public function __construct(PhpNamespace $phpNamespace)
    {
        $this->phpNamespace = $phpNamespace;
    }

    /**
     * Prints array with short tags
     *
     * @param array $list
     * @param int $indentationLevel
     * @param string $closingChar
     * @param null|mixed $key
     * @param bool $shouldShowPrefixBracket
     * @return string
     */
    public function printArray(
        array $list,
        int $indentationLevel = 1,
        string $closingChar = ",\n",
        $key = null,
        $shouldShowPrefixBracket = true
    ): string {
        $parts = [];
        $this->iterateArray($list, $parts, $indentationLevel);

        $bracketsSpaces = $this->getSpaces(($indentationLevel - 1) * 4);
        if ($key === null) {
            $keyPart = '';
        } else {
            $keyPart = $this->varExport($key) . ' => ';
        }

        if ($shouldShowPrefixBracket) {
            $prefixBracket = $bracketsSpaces;
        } else {
            $prefixBracket = '';
        }

        return $prefixBracket . $keyPart . "[\n" . implode("\n", $parts) . "\n" . $bracketsSpaces . ']' . $closingChar;
    }

    private function iterateArray(array $list, array &$parts, int$indentationLevel): void
    {
        $isList = ArrayUtils::isSequential($list);
        foreach ($list as $itemKey => $item) {
            if (is_array($item)) {
                if ($isList) {
                    $itemKey = null;
                }
                $parts[] = $this->printArray($item, $indentationLevel + 1, ',', $itemKey);
            } else {
                $spacesCount = $indentationLevel * 4;
                if ($isList) {
                    $keyLiteral = '';
                } else {
                    $keyLiteral = $this->varExport($itemKey) . ' => ';
                }
                $parts[] = $this->getSpaces($spacesCount) . $keyLiteral . $this->varExport($item) . ',';
            }
        }
    }

    private function getSpaces($count): string
    {
        $spaces = '';
        while ($count > 0) {
            $spaces .= ' ';
            $count -= 1;
        }

        return $spaces;
    }

    private function varExport($variable): string
    {
        if ($variable instanceof PhpLiteral) {
            if (strpos((string)$variable, '::class') !== false) {
                $className = str_replace('::class', '', (string)$variable);
                return $this->phpNamespace->unresolveName($className) . '::class';
            } else {
                return (string)$variable;
            }
        }
        // firs check for ::class strings
        if (is_string($variable) && (strpos($variable, '::class') !== false)) {
            $className = str_replace('::class', '', $variable);
            return $this->phpNamespace->unresolveName($className) . '::class';
        }
        // then checks for backslash
        if (is_string($variable) && (strpos($variable, '\\') !== false)) {
            return $this->phpNamespace->unresolveName($variable) . '::class';
        }
        return var_export($variable, true);
    }
}
