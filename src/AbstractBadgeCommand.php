<?php

declare(strict_types=1);

namespace Loom\Badger;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AbstractBadgeCommand extends Command
{
    protected SymfonyStyle $style;
    protected string $projectPath;
    protected ?string $composerJsonPath = null;
    protected ?array $composerJson = null;
    protected ?string $readmePath = null;
    protected bool $requireComposerJson = false;

    protected function configure(): void
    {
        $this->addArgument('project', InputArgument::REQUIRED, 'The path to the project directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->style = new SymfonyStyle($input, $output);
        $this->projectPath = $input->getArgument('project');

        if (!file_exists($this->projectPath) || !is_dir($this->projectPath)) {
            $this->style->error('The project path specified is invalid.');

            return Command::FAILURE;
        }

        if ($this->requireComposerJson) {
            $composerJsonPath = sprintf('%s/composer.json', $this->projectPath);

            if (!file_exists($composerJsonPath)) {
                $this->style->error('The project does not have a composer.json file.');

                return Command::FAILURE;
            }

            $this->composerJsonPath = $composerJsonPath;
            $this->composerJson = json_decode(file_get_contents($composerJsonPath), true);
        }

        $this->readmePath = sprintf('%s/README.md', $this->projectPath);

        if (!file_exists($this->readmePath)) {
            $this->style->error('The README.md file does not exist.');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function getBasePattern(string $badge, string $altText): string
    {
        return sprintf('/<img src="https:\/\/img\.shields\.io\/badge\/%s" alt="%s">/', $badge, $altText);
    }
}