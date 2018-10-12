<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Util;

use PHPUnit\Framework\TestCase;

final class ArrayUtilsTest extends TestCase
{
    /**
     * @param array $data
     * @param bool $expected
     *
     * @dataProvider listProvider
     */
    public function testIsSequential(array $data, bool $expected): void
    {
        $this->assertSame($expected, ArrayUtils::isSequential($data));
    }

    public function listProvider(): array
    {
        return [
            [
                [1, 2, 3],
                true
            ],
            [
                ['a', 'b', 'c'],
                true
            ],
            [
                ['a', 'b', 'c', null, 'd'],
                true
            ],
            [
                ['a' => 'b'],
                false
            ],
            [
                [1 => 'a', 2 => 'b'],
                false
            ],
            [
                [1 => 'a', 2 => 'b', 0 => 'd'],
                true
            ],
        ];
    }

    /**
     * @param array $firstData
     * @param array $secondData
     * @param array $merged
     *
     * @dataProvider arrayProvider
     */
    public function testMerge(array $firstData, array $secondData, array $merged): void
    {
        $this->assertSame($merged, ArrayUtils::merge($firstData, $secondData));
    }

    public function arrayProvider(): array
    {
        return [
            // test equal lists (result is unique)
            [
                [1, 2],
                [1, 2],
                [1, 2],
            ],
            // test associated lists
            [
                [1, 2],
                [1 => 3],
                [1, 3],
            ],
            // test combined
            [
                ['key' => [1, 2]],
                ['key' => [2, 3, 4]],
                ['key' => [1, 2, 3, 4]], // result is unique
            ],
            // test mixed
            [
                ['a' => 'z', 'bar' => 'baz'],
                [1, 2],
                ['a' => 'z', 'bar' => 'baz', 1, 2],
            ],
            // test mixed 2
            [
                [1 => 2, 3 => 4],
                [1, 2],
                [1 => 2, 3 => 4, 1],
            ],
            // test lists 2
            [
                [1, 3, 5],
                [1, 2, 4, 6],
                [1, 3, 5, 2, 4, 6],
            ],
            // test deep structured
            [
                [
                    1 => [
                        2 => [
                            3 => 4
                        ],
                    ],
                ],
                [
                    1 => [
                        2 => [
                            3
                        ],
                    ],
                    5
                ],
                [
                    1 => [
                        2 => [
                            3 => 4,
                            3
                        ],
                    ],
                    5
                ],
            ],
        ];
    }
}
