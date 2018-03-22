<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Writer;

class FileWriter implements WriteInterface
{
    /**
     * @var string
     */
    private $baseDir;
    /**
     * @var FileNameProviderInterface
     */
    private $fileNameProvider;

    public function __construct(string $baseDir, FileNameProviderInterface $fileNameProvider)
    {
        $this->baseDir = $baseDir;
        $this->fileNameProvider = $fileNameProvider;
    }

    public function write(string $content): void
    {
        $fullName = rtrim($this->baseDir, '/') . '/' . $this->fileNameProvider->getFileName($content);

        $dir = dirname($fullName);

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($fullName, $content);
    }
}
