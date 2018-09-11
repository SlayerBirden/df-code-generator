<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Writer;

class FileWriter implements WriteInterface
{
    /**
     * @var string
     */
    private $baseDir;

    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;
    }

    public function write(string $content, string $fileName): void
    {
        $fullName = rtrim($this->baseDir, '/') . '/' . $fileName;

        $dir = dirname($fullName);

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($fullName, $content);
    }
}
