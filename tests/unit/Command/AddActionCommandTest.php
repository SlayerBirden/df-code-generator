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

class AddActionCommandTest extends TestCase
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
    }

    public function testExecuteWithTestsDryRun()
    {
    }

    public function testExecuteWithoutTestsForce()
    {
    }

    public function testExecuteWithTestsForce()
    {
    }
}
