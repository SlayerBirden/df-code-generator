<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Writer;

use Zend\Code\Reflection\FileReflection;
use Zend\Filter\Word\CamelCaseToSeparator;

class StandardFileNameProvider implements FileNameProviderInterface
{
    public function getFileName(string $contents)
    {
        $tmpFile = tempnam(sys_get_temp_dir(), "generation");
        file_put_contents($tmpFile, $contents);

        include $tmpFile;
        $fileReflection = new FileReflection($tmpFile);

        return $this->getFileNameFromClassName($fileReflection->getClass()->getName());
    }

    private function getFileNameFromClassName(string $className): string
    {
        // assumption is that first 2 parts of the namespace are PSR4 "src" dir
        $parts = explode('\\', $className);
        if (count($parts) === 1) {
            $name = reset($parts);
            $nameParts = explode(':', (new CamelCaseToSeparator(':'))->filter($name));
            if (count($nameParts) === 3) {
                $shortName = rtrim(strtolower($nameParts[1]), 's');
            } else {
                $shortName = uniqid();
            }
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
}
