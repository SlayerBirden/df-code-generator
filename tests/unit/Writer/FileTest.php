<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Writer;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    public function testWrite()
    {
        $root = vfsStream::setup();

        $writer = new FileWriter($root->url());

        $writer->write('hello', 'src/dummy.php');

        $fName = $root->url() . '/src/dummy.php';
        $this->assertFileExists($fName);
        $this->assertEquals('hello', file_get_contents($fName));
    }
}
