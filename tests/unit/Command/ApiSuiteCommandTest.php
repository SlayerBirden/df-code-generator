<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use SlayerBirden\DFCodeGeneration\Util\CodeLoader;
use SlayerBirden\DFCodeGeneration\Writer\WriteInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ApiSuiteCommandTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $writer;
    /**
     * @var CommandTester
     */
    private $tester;

    public static function setUpBeforeClass()
    {
        $hubbyBody = <<<'HUBBY'
<?php
use Doctrine\ORM\Mapping as ORM;
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
HUBBY;
        CodeLoader::loadCode($hubbyBody, 'hubbyEntity.php');

    }

    protected function setUp()
    {
        $app = new Application();

        $this->writer = $this->prophesize(WriteInterface::class);

        $app->add(new ApiSuiteCommand(null, $this->writer->reveal()));

        $command = $app->find('generate:api');
        $this->tester = new CommandTester($command);
    }

    public function testExecuteWithoutTestsDryRun()
    {
        $this->tester->execute([
            'command' => 'generate:api',
            'entity' => 'Hubby',
        ]);
        $output = $this->tester->getDisplay();

        # controllers
        $this->assertContains('AddHubbyAction', $output);
        $this->assertContains('UpdateHubbyAction', $output);
        $this->assertContains('GetHubbyAction', $output);
        $this->assertContains('GetHubbiesAction', $output);
        $this->assertContains('DeleteHubbyAction', $output);
        # else
        $this->assertContains('HubbyRoutesDelegator', $output);
        $this->assertContains('ConfigProvider', $output);

        # no tests
        $this->assertNotContains('AddHubbyCest', $output);
    }

    public function testExecuteWithTestsDryRun()
    {
        $this->tester->execute([
            'command' => 'generate:api',
            'entity' => 'Hubby',
            '--tests' => true,
        ]);
        $output = $this->tester->getDisplay();

        # controllers
        $this->assertContains('AddHubbyAction', $output);
        $this->assertContains('UpdateHubbyAction', $output);
        $this->assertContains('GetHubbyAction', $output);
        $this->assertContains('GetHubbiesAction', $output);
        $this->assertContains('DeleteHubbyAction', $output);
        # else
        $this->assertContains('HubbyRoutesDelegator', $output);
        $this->assertContains('ConfigProvider', $output);
        # tests
        $this->assertContains('AddHubbyCest', $output);
        $this->assertContains('DeleteHubbyCest', $output);
        $this->assertContains('GetHubbyCest', $output);
        $this->assertContains('GetHubbiesCest', $output);
        $this->assertContains('UpdateHubbyCest', $output);
    }

    public function testExecuteWithoutTestsForce()
    {
        $this->tester->execute([
            'command' => 'generate:api',
            'entity' => 'Hubby',
            '--force' => true,
        ]);

        $this->writer->write(Argument::type('string'))->shouldHaveBeenCalledTimes(7);

        $output = $this->tester->getDisplay();
        $this->assertNotEmpty($output);
    }

    public function testExecuteWithTestsForce()
    {
        $this->tester->execute([
            'command' => 'generate:api',
            'entity' => 'Hubby',
            '--tests' => true,
            '--force' => true,
        ]);

        $this->writer->write(Argument::type('string'))->shouldHaveBeenCalledTimes(12);

        $output = $this->tester->getDisplay();
        $this->assertNotEmpty($output);
    }
}
