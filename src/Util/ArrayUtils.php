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

    /**
     * Recursively merges/replaces array $a and array $b.
     *
     * If determined that $b is list, it is appended to $a and the resulting array is made unique
     * Otherwise values from $b replaces values from $a with the same keys
     *
     * @param array $a
     * @param array $b
     * @return array
     */
    public static function merge(array $a, array $b): array
    {
        if (self::isSequential($b)) {
            foreach ($b as $value) {
                $a[] = $value;
            }
            return array_unique($a);
        }

        foreach ($b as $key => $value) {
            if (array_key_exists($key, $a)) {
                if (is_array($value)) {
                    $a[$key] = self::merge($a[$key], $value);
                } else {
                    $a[$key] = $value;
                }
            } else {
                $a[$key] = $value;
            }
        }

        return $a;
    }
}
