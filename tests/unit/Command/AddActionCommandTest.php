<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Command;

use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\TestCase;
use SlayerBirden\DFCodeGeneration\Command\Controllers\AddActionCommand;
use SlayerBirden\DFCodeGeneration\Writer\WriteInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @ORM\Entity @ORM\Table(name="hubbies")
 */
class Hubby
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @var string
     */
    private $hubbyId;
    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $name;
}

class AddActionCommandTest extends TestCase
{
    public function testExecuteDryRun()
    {
        $writer = $this->prophesize(WriteInterface::class);
        $tester = new CommandTester(new AddActionCommand(null, $writer->reveal()));
        $tester->execute([
            'entity' => Hubby::class,
        ]);

        $display = $tester->getDisplay();

        # controller
        $this->assertContains('AddHubbyAction', $display);
        # config
        $this->assertContains('ConfigProvider', $display);
        # factories
        $this->assertContains('HubbyHydratorFactory', $display);
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
        $tester = new CommandTester(new AddActionCommand(null, $writer));
        $tester->execute([
            'entity' => Hubby::class,
            '--force' => true,
        ]);

        $content = $writer->content;

        $this->assertContains('AddHubbyAction', $content);
        # config
        $this->assertContains('ConfigProvider', $content);
        # factories
        $this->assertContains('HubbyHydratorFactory', $content);
    }
}
