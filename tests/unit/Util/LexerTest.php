<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Util;

use PHPUnit\Framework\TestCase;

final class LexerTest extends TestCase
{
    /**
     * @param string $fullName
     * @param string $expected
     *
     * @dataProvider getNames
     */
    public function testGetBaseName(string $fullName, string $expected): void
    {
        $this->assertSame($expected, Lexer::getBaseName($fullName));
    }

    public function getNames(): array
    {
        return [
            ['\\', ''],
            ['Something\Else', 'Else'],
            ['Something\Else\\', ''],
            ['\Something\Else', 'Else'],
            ['JustName', 'JustName'],
        ];
    }

    /**
     * @param string $fullName
     * @param string $expected
     *
     * @dataProvider getRefNames
     */
    public function testGetRefName(string $fullName, string $expected): void
    {
        $this->assertSame($expected, Lexer::getRefName($fullName));
    }

    public function getRefNames(): array
    {
        return [
            ['\\', ''],
            ['Something\Else', 'else'],
            ['\Something\ElseY', 'else_y'],
            ['JustName', 'just_name'],
            ['Boo\jLib', 'j_lib'],
        ];
    }

    /**
     * @param string $single
     * @param string $expected
     *
     * @dataProvider getNamesForTransform
     */
    public function testGetPluralForm(string $single, string $expected): void
    {
        $this->assertSame($expected, Lexer::getPluralForm($single));
    }

    public function getNamesForTransform(): array
    {
        return [
            ['cat', 'cats'],
            ['bus', 'buses'],
            ['village', 'villages'],
            ['registry', 'registries'],
        ];
    }

    /**
     * @param string $expected
     * @param string $plural
     *
     * @dataProvider getNamesForTransform
     */
    public function testGetSingularForm(string $expected, string $plural): void
    {
        $this->assertSame($expected, Lexer::getSingularForm($plural));
    }
}
