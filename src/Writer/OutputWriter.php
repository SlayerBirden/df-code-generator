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

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function write(string $content, string $fileName): void
    {
        $this->output->writeln(
            sprintf('<comment>START FILE %s==================================================</comment>', $fileName)
        );
        $this->output->writeln($content);
        $this->output->writeln(
            sprintf('<comment>END FILE %s####################################################</comment>', $fileName)
        );
        $this->output->writeln("<comment>#\n#\n#\n#</comment>");
    }
}
