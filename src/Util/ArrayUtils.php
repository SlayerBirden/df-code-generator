<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Util;

final class ArrayUtils
{
    public static function isSequential(array $list): bool
    {
        $i = 0;
        $count = count($list);
        while (isset($list[$i])) {
            $i += 1;
            if ($i === $count) {
                return true;
            }
        }

        return false;
    }
}
