<?php

declare(strict_types=1);

namespace Loom\Badger;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('badge:license')]
class LicenseBadge extends AbstractBadgeCommand
{
    protected bool $requireComposerJson = true;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (parent::execute($input, $output) === Command::FAILURE) {
            return Command::FAILURE;
        }

        $license = $this->composerJson['license'] ?? null;

        if (!$license) {
            $this->style->error('The project does not have a license specified in its composer.json.');

            return Command::FAILURE;
        }

        $readmeContent = file_get_contents($this->readmePath);
        $newLicenseBadge = sprintf(
            '<img src="https://img.shields.io/badge/License-%s-40adbc" alt="License %s">',
            str_replace('-', '--', $license),
            $license
        );

        $pattern = $this->getBasePattern('License-.*?-40adbc', 'License .*?');

        if (preg_match($pattern, $readmeContent)) {
            $readmeContent = preg_replace($pattern, $newLicenseBadge, $readmeContent);
            $successMessage = 'License badge updated successfully.';
        } else {
            $badgeSection = "<!-- License Badge -->\n$newLicenseBadge";
            $readmeContent = str_replace('<!-- License Badge -->', $badgeSection, $readmeContent);
            $successMessage = 'License badge added successfully.';
        }

        if (file_put_contents($this->readmePath, $readmeContent) === false) {
            $this->style->error('Unable to write to README.md file.');
            return Command::FAILURE;
        }

        $this->style->success($successMessage);

        return Command::SUCCESS;
    }
}