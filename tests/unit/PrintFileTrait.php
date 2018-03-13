<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration;

trait PrintFileTrait
{
    public function getPrintableFile(string $contents): string
    {
        $lines = explode(PHP_EOL, $contents);
        $i = 1;
        return implode(PHP_EOL, array_map(function ($line) use (&$i) {
            return $i++ . ':' . $line;
        }, $lines));
    }
}
