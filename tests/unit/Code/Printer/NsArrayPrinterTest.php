<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Code\Printer;

use Nette\PhpGenerator\PhpLiteral;
use Nette\PhpGenerator\PhpNamespace;
use PHPUnit\Framework\TestCase;

class A
{
    public $a;
    public static function __set_state($an_array)
    {
        $obj = new A();
        $obj->a = $an_array['a'];
        return $obj;
    }
}

final class NsArrayPrinterTest extends TestCase
{
    /**
     * @param array $testedArray
     * @param PhpNamespace $namespace
     * @param string $expected
     *
     * @dataProvider arrayProvider
     */
    public function testPrintArray(array $testedArray, PhpNamespace $namespace, string $expected): void
    {
        $printer = new NsArrayPrinter($namespace);
        $this->assertSame($expected, $printer->printArray($testedArray));
    }

    public function arrayProvider(): array
    {
        $ns = new PhpNamespace('bar');
        $ns->addUse('A\B\C');

        return [
            [
                [1, 2, 3],
                $ns,
                <<<ARRAY
[
    1,
    2,
    3,
],

ARRAY

            ],
            [
                [1, ['a' => 'b'], new PhpLiteral('const')],
                $ns,
                <<<ARRAY
[
    1,
    [
        'a' => 'b',
    ],
    const,
],

ARRAY

            ],
            [
                [1, ['a' => 'b', 2]],
                $ns,
                <<<ARRAY
[
    1,
    [
        'a' => 'b',
        0 => 2,
    ],
],

ARRAY

            ],
            [
                ['A\B\C::class', '\A\B\D::class'],
                $ns,
                <<<ARRAY
[
    C::class,
    \A\B\D::class,
],

ARRAY

            ],
        ];
    }
}
