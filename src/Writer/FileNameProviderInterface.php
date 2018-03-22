<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Writer;

interface FileNameProviderInterface
{
    public function getFileName(string $contents);
}
