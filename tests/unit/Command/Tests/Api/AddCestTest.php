<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Command\Tests\Api;

use PHPUnit\Framework\TestCase;
use SlayerBirden\DFCodeGeneration\Command\Hubby;
use SlayerBirden\DFCodeGeneration\Writer\WriteInterface;
use Symfony\Component\Console\Tester\CommandTester;

final class AddCestTest extends TestCase
{
    public function testExecuteDryRun()
    {
        $writer = $this->prophesize(WriteInterface::class);
        $tester = new CommandTester(new AddTestCommand(null, $writer->reveal()));
        $tester->execute([
            'entity' => Hubby::class,
        ]);

        $display = $tester->getDisplay();

        $this->assertContains('AddCest', $display);
    }

    public function testExecuteForce()
    {
        $writer = new class implements WriteInterface {
            public $content = '';

            public function write(string $content, string $fileName): void
            {
                $this->content .= $content;
            }
        };
        $tester = new CommandTester(new AddTestCommand(null, $writer));
        $tester->execute([
            'entity' => Hubby::class,
            '--force' => true,
        ]);

        $content = $writer->content;

        $this->assertContains('AddCest', $content);
    }
}
