<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Command\Tests\Api;

use SlayerBirden\DFCodeGeneration\Command\AbstractApiCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class AllTestsCommand extends AbstractApiCommand
{
    private $commands = [
        'test:api:add',
        'test:api:delete',
        'test:api:get',
        'test:api:gets',
        'test:api:update',
    ];

    protected function configure()
    {
        parent::configure();
        $this->setName('test:api:all')
            ->setDescription('Api Tests for all actions.')
            ->setHelp('This command creates the Codeception Api Tests for all Actions for given entity.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->commands as $commandName) {
            $command = $this->getApplication()->find($commandName);

            $arguments = array(
                'entity' => $this->entityClassName,
                '--force' => $this->force,
            );
            $greetInput = new ArrayInput($arguments);
            $command->run($greetInput, $output);
        }
    }
}
