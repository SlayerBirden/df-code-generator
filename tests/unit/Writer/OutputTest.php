<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Writer;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Output\OutputInterface;

class OutputTest extends TestCase
{
    public function testWrite()
    {
        $fileNameProvider = $this->prophesize(FileNameProviderInterface::class);
        $output = $this->prophesize(OutputInterface::class);

        $writer = new OutputWriter($output->reveal(), $fileNameProvider->reveal());
        $writer->write('hello');

        $output->writeln(Argument::exact('hello'))->shouldHaveBeenCalled();
    }
}
