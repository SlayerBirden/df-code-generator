<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class CodeLoader
{
    /**
     * @var vfsStreamDirectory
     */
    static private $root ;
    static private $declared = [];

    static private function init()
    {
        if (self::$root === null) {
            self::$root = vfsStream::setup();
        }
    }

    static public function loadCode(string $code, string $fileName): void
    {
        self::init();
        if (!self::isDeclared($code)) {
            file_put_contents(self::$root->url() . "/$fileName", $code);
            include self::$root->url() . "/$fileName";
            self::$declared[] = md5($code);
        }
    }

    static private function isDeclared(string $code): bool
    {
        return in_array(md5($code), self::$declared);
    }
}
