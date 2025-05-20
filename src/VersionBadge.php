<?php

declare(strict_types=1);

namespace Loom\Badger;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('badge:version')]
class VersionBadge extends AbstractBadgeCommand
{
    protected bool $requireComposerJson = true;

    protected function configure(): void
    {
        $this->addArgument('project', InputArgument::REQUIRED, 'The JSON file containing version information');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (parent::execute($input, $output) === Command::FAILURE) {
            return Command::FAILURE;
        }

        $version = $this->composerJson['version'] ?? null;

        if (!$version) {
            $this->style->error('The project does not have a version specified in its composer.json.');

            return Command::FAILURE;
        }

        $readmeContent = file_get_contents($this->readmePath);
        $newVersionBadge = sprintf(
            '<img src="https://img.shields.io/badge/Version-%s-blue" alt="Version %s">',
            $version,
            $version
        );

        $pattern = $this->getBasePattern('Version-.*?-blue', 'Version .*?');

        if (preg_match($pattern, $readmeContent)) {
            $readmeContent = preg_replace($pattern, $newVersionBadge, $readmeContent);
            $successMessage = 'Version badge updated successfully.';
        } else {
            $badgeSection = "<!-- Version Badge -->\n$newVersionBadge";
            $readmeContent = str_replace('<!-- Version Badge -->', $badgeSection, $readmeContent);
            $successMessage = 'Version badge added successfully.';
        }

        if (file_put_contents($this->readmePath, $readmeContent) === false) {
            $this->style->error('Unable to write to README.md file.');
            return Command::FAILURE;
        }

        $this->style->success($successMessage);

        return Command::SUCCESS;
    }

}