<?php

namespace Magephi\Command\Environment;

use Magephi\Command\AbstractCommand;
use Magephi\Entity\Environment;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to stop the environment. The install command must have been executed before.
 */
class StopCommand extends AbstractEnvironmentCommand
{
    protected $command = 'stop';

    public function getPrerequisites(): array
    {
        $prerequisites = parent::getPrerequisites();
        $prerequisites['binary'] = array_merge($prerequisites['binary'], ['Mutagen']);

        return $prerequisites;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->setDescription('Stop environment, equivalent to <fg=yellow>make stop</>')
            ->setHelp(
                'This command allows you to stop your Magento 2 environment. It must have been installed before.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $environment = new Environment();

        $this->interactive->section('Stopping environment');

        $process = $this->processFactory->runProcessWithProgressBar(
            ['make', 'stop'],
            60,
            function ($type, $buffer) {
                return stripos($buffer, 'stopping') && stripos($buffer, 'done');
            },
            $output,
            $environment->getContainers() + 1
        );
        $this->interactive->newLine(2);

        if (!$process->getProcess()->isSuccessful()) {
            $this->interactive->error(
                [
                    "Environment couldn't be stopped: ",
                    $process->getProcess()->getErrorOutput(),
                ]
            );

            return AbstractCommand::CODE_ERROR;
        }

        $this->interactive->success('Environment stopped.');

        return AbstractCommand::CODE_SUCCESS;
    }
}