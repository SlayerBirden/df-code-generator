<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Writer;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class FileTest extends TestCase
{
    public function testWrite()
    {
        $fileNameProvider = $this->prophesize(FileNameProviderInterface::class);
        $fileNameProvider->getFileName(Argument::any())->willReturn('src/dummy.php');

        $root = vfsStream::setup();

        $writer = new FileWriter($root->url(), $fileNameProvider->reveal());

        $writer->write('hello');

        $fName = $root->url() . '/src/dummy.php';
        $this->assertFileExists($fName);
        $this->assertEquals('hello', file_get_contents($fName));
    }
}
