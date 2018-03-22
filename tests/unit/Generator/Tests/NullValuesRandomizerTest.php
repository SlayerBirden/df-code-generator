<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

use PHPUnit\Framework\TestCase;

class NullValuesRandomizerTest extends TestCase
{
    /**
     * @dataProvider percentageProvider
     *
     * @param float $percentage
     */
    public function testPercentage(float $percentage)
    {
        $randomizer = new NullValuesRandomizer($percentage);
        $hits = 0;
        $total = 1000000;
        for ($i = 0; $i < $total; ++$i) {
            $randomizer->ifShouldWrite() ? ++$hits : null;
        }

        $expected = (int)($percentage * 100);
        $actual = round($hits / $total * 100);

        $this->assertThat($actual, new RangeConstraint($expected - 1, $expected + 1));
    }

    public function testThatZeroNeverCalled()
    {
        $randomizer = new NullValuesRandomizer(.0);

        $hits = 0;
        for ($i = 0; $i < 1000000; ++$i) {
            $randomizer->ifShouldWrite() ? ++$hits : null;
        }

        $this->assertSame(0, $hits);
    }

    public function percentageProvider(): array
    {
        return [
            [.1],
            [.5],
            [.0],
        ];
    }
}
