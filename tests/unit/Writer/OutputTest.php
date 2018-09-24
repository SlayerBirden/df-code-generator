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
        $output = $this->prophesize(OutputInterface::class);

        $writer = new OutputWriter($output->reveal());
        $writer->write('hello', 'testfile');

        $output->writeln(Argument::exact('hello'))->shouldHaveBeenCalled();
    }
}
