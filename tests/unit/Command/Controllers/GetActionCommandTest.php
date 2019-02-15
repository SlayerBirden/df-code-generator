<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Command\Controllers;

use PHPUnit\Framework\TestCase;
use SlayerBirden\DFCodeGeneration\Command\Hubby;
use SlayerBirden\DFCodeGeneration\Generator\Config\FileConfigProvider;
use SlayerBirden\DFCodeGeneration\Writer\WriteInterface;
use Symfony\Component\Console\Tester\CommandTester;

final class GetActionCommandTest extends TestCase
{
    public function testExecuteDryRun()
    {
        $writer = $this->prophesize(WriteInterface::class);
        $tester = new CommandTester(new GetActionCommand(null, $writer->reveal(), new FileConfigProvider()));
        $tester->execute([
            'entity' => Hubby::class,
        ]);

        $display = $tester->getDisplay();

        # controller
        $this->assertContains('GetHubbyAction', $display);
        # config
        $this->assertContains('ConfigProvider', $display);
        # factories
        $this->assertContains('HubbyHydratorFactory', $display);
        $this->assertContains('HubbyResourceMiddlewareFactory', $display);
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
        $tester = new CommandTester(new GetActionCommand(null, $writer, new FileConfigProvider()));
        $tester->execute([
            'entity' => Hubby::class,
            '--force' => true,
        ]);

        $content = $writer->content;

        $this->assertContains('GetHubbyAction', $content);
        # config
        $this->assertContains('ConfigProvider', $content);
        # factories
        $this->assertContains('HubbyHydratorFactory', $content);
        $this->assertContains('HubbyResourceMiddlewareFactory', $content);
    }
}
