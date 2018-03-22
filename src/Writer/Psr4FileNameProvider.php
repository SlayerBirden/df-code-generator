<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Writer;

use SlayerBirden\DFCodeGeneration\Util\CodeLoader;
use SlayerBirden\DFCodeGeneration\Util\Lexer;
use Zend\Code\Reflection\FileReflection;
use Zend\Code\Scanner\CachingFileScanner;
use Zend\Code\Scanner\ClassScanner;
use Zend\Filter\Word\CamelCaseToSeparator;

class Psr4FileNameProvider implements FileNameProviderInterface
{
    private $suffixes = [
        'cest'
    ];

    private $prefixes = [
        'get',
        'add',
        'create',
        'delete',
        'remove',
        'update'
    ];

    public function getFileName(string $contents)
    {
        $tmpFile = sys_get_temp_dir() . '/' . uniqid('generation');
        file_put_contents($tmpFile, $contents);
        $scanner = new CachingFileScanner($tmpFile);

        $classes = $scanner->getClasses();
        /** @var ClassScanner $class */
        $class = reset($classes);

        return $this->getFileNameFromClassName($class->getName());
    }

    private function getFileNameFromClassName(string $className): string
    {
        // assumption is that first 2 parts of the namespace are PSR4 "src" dir
        $parts = explode('\\', $className);
        if (count($parts) === 1) {
            $name = reset($parts);
            $shortName = strtolower(Lexer::getSingularForm($this->purgeName($name)));
            $paths = [
                'tests',
                'api',
                $shortName,
                $name . '.php',
            ];
        } else {
            $paths = array_slice($parts, 2, -1);
            $file = end($parts);

            array_unshift($paths, 'src');
            array_push($paths, $file . '.php');
        }

        return implode('/', $paths);
    }

    /**
     * Remove prefixes and suffixes from the name
     *
     * @param string $name
     * @return string
     */
    public function purgeName(string $name): string
    {
        $nameParts = explode(':', (new CamelCaseToSeparator(':'))->filter($name));

        switch (count($nameParts)) {
            case 3:
                if (in_array(strtolower($nameParts[0]), $this->prefixes, true)) {
                    unset($nameParts[0]);
                }
                if (in_array(strtolower($nameParts[2]), $this->suffixes, true)) {
                    unset($nameParts[2]);
                }
                break;
            case 2:
                if (in_array(strtolower($nameParts[0]), $this->prefixes, true)) {
                    unset($nameParts[0]);
                }
                if (in_array(strtolower($nameParts[1]), $this->suffixes, true)) {
                    unset($nameParts[1]);
                }
                break;
        }

        if (empty($nameParts)) {
            return uniqid('generated');
        }

        return implode($nameParts);
    }
}
