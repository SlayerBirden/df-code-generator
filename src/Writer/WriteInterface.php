<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Writer;

interface WriteInterface
{
    public function write(string $content, string $fileName): void;
}
