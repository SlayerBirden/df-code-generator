<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Writer;

final class FileWriter implements WriteInterface
{
    const CONFIG_FILE_NAME = 'ConfigProvider.php';

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
        if (!file_exists($fullName) || basename($fullName) === self::CONFIG_FILE_NAME) {
            file_put_contents($fullName, $content);
        }
    }
}
