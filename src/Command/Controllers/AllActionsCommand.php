<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Command\Controllers;

use SlayerBirden\DFCodeGeneration\Command\AbstractApiCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class AllActionsCommand extends AbstractApiCommand
{
    private $commands = [
        'action:add',
        'action:delete',
        'action:get',
        'action:gets',
        'action:update',
    ];

    protected function configure()
    {
        parent::configure();
        $this->setName('action:all')
            ->setDescription('All action controllers and support configuration.')
            ->setHelp('This command creates all Actions for given entity.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->commands as $commandName) {
            /** @var AbstractApiCommand $command */
            $command = $this->getApplication()->find($commandName);
            $command->setConfigProvider($this->getConfigProvider());

            $arguments = array(
                'command' => $commandName,
                'entity' => $this->entityClassName,
                '--force' => $this->force,
            );
            $greetInput = new ArrayInput($arguments);
            $command->run($greetInput, $output);
        }
    }
}
