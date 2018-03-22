<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Writer;

use Symfony\Component\Console\Output\OutputInterface;

class OutputWriter implements WriteInterface
{
    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var FileNameProviderInterface
     */
    private $fileNameProvider;

    public function __construct(OutputInterface $output, FileNameProviderInterface $fileNameProvider)
    {
        $this->output = $output;
        $this->fileNameProvider = $fileNameProvider;
    }

    public function write(string $content): void
    {
        $fName = $this->fileNameProvider->getFileName($content);
        $this->output->writeln(
            sprintf('<comment>START FILE %s==================================================</comment>', $fName)
        );
        $this->output->writeln($content);
        $this->output->writeln(
            sprintf('<comment>END FILE %s####################################################</comment>', $fName)
        );
        $this->output->writeln("<comment>#\n#\n#\n#</comment>");
    }
}
