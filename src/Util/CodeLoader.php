<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Util;

class CodeLoader
{
    private static $declared = [];

    public static function loadCode(string $code, string $fileName): void
    {
        if (!self::isDeclared($code)) {
            if (strpos($fileName, '/') !== false) {
                $tmpFile = $fileName;
            } else {
                $tmpFile = sys_get_temp_dir() . "/$fileName";
            }
            file_put_contents($tmpFile, $code);
            include $tmpFile;
            self::$declared[] = md5($code);
        }
    }

    private static function isDeclared(string $code): bool
    {
        return in_array(md5($code), self::$declared);
    }
}
