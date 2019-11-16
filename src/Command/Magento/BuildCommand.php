<?php

namespace Magephi\Command\Magento;

use Magephi\Component\DockerCompose;
use Magephi\Component\ProcessFactory;
use Magephi\Helper\Installation;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to build containers for the environment.
 */
class BuildCommand extends AbstractMagentoCommand
{
    protected $command = 'build';

    /** @var Installation */
    private $installation;

    public function __construct(
        ProcessFactory $processFactory,
        DockerCompose $dockerCompose,
        Installation $installation,
        string $name = null
    ) {
        parent::__construct($processFactory, $dockerCompose, $name);
        $this->installation = $installation;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->installation->setOutputInterface($output);
    }

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription('Build environment, equivalent to <fg=yellow>make build</>')
            ->setHelp(
                'This command allows you to build container for your Magento 2 environment.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->interactive->section('Building environment');

        $process = $this->installation->buildMake();

        $this->interactive->newLine(2);
        $this->interactive->success('Containers have been built.');

        if (!$process->isSuccessful()) {
            $this->interactive->newLine(2);
            $this->interactive->error($process->getErrorOutput());
            $this->interactive->note(
                [
                    "Ensure you're not using a deleted branch for package emakinafr/docker-magento2.",
                    'This issue may came from a missing package in the PHP dockerfile after a version upgrade.',
                ]
            );

            return 1;
        }

        return null;
    }
}