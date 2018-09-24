<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Util;

use Zend\Filter\Word\CamelCaseToUnderscore;

final class Lexer
{
    /**
     * Convert plural into singular form
     *  # buses => bus
     *  # products => product
     *  # stores => store
     *  # categories => category
     *
     * @param string $name
     * @return string
     */
    public static function getSingularForm(string $name): string
    {
        if (preg_match('/.*ies$/', $name)) {
            return preg_replace('/(.*)ies$/', '$1y', $name);
        } elseif (preg_match('/.*ses$/', $name)) {
            return preg_replace('/(.*)ses$/', '$1s', $name);
        } elseif (preg_match('/.*s$/', $name)) {
            return preg_replace('/(.*)s$/', '$1', $name);
        }

        return $name;
    }

    /**
     * Convert singular into plural form
     *  # bus => buses
     *  # product => products
     *  # store => stores
     *  # category => categories
     *
     * @param string $name
     * @return string
     */
    public static function getPluralForm(string $name): string
    {
        if (preg_match('/.*s$/', $name)) {
            return preg_replace('/(.*)s$/', '$1ses', $name);
        } elseif (preg_match('/.*y$/', $name)) {
            return preg_replace('/(.*)y$/', '$1ies', $name);
        }

        return $name . 's';
    }

    /**
     * @param string $fullyQualifiedName
     * @return string
     * @throws \ReflectionException
     */
    public static function getBaseName(string $fullyQualifiedName): string
    {
        $reflection = new \ReflectionClass($fullyQualifiedName);
        return $reflection->getShortName();
    }

    /**
     * @param string $fullyQualifiedName
     * @return string
     * @throws \ReflectionException
     */
    public static function getRefName(string $fullyQualifiedName): string
    {
        $baseName = self::getBaseName($fullyQualifiedName);
        return strtolower((new CamelCaseToUnderscore())->filter($baseName));
    }
}
