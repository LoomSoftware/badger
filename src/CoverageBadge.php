<?php

declare(strict_types=1);

namespace Loom\Badger;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('badge:coverage')]
class CoverageBadge extends AbstractBadgeCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (parent::execute($input, $output) === Command::FAILURE) {
            return Command::FAILURE;
        }

        $coverageXmlPath = $this->projectPath . '/coverage/coverage.xml';

        if (!file_exists($coverageXmlPath)) {
            $this->style->error('Coverage file not found: ' . $coverageXmlPath);
            return Command::FAILURE;
        }

        $xml = simplexml_load_file($coverageXmlPath);

        if ($xml === false) {
            $this->style->error('Failed to parse coverage XML file.');
            return Command::FAILURE;
        }

        $metrics = $xml->xpath('//metrics');
        if (empty($metrics)) {
            $this->style->error('No metrics found in coverage XML file.');
            return Command::FAILURE;
        }

        $totalElements = 0;
        $coveredElements = 0;

        foreach ($metrics as $metric) {
            $totalElements += (int) $metric['elements'];
            $coveredElements += (int) $metric['coveredelements'];
        }

        if ($totalElements === 0) {
            $this->style->error('No code elements found in coverage report.');
            return Command::FAILURE;
        }

        $coveragePercentage = ($coveredElements / $totalElements) * 100;
        $coverageFormatted = number_format($coveragePercentage, 2);
        $badgeColour = match(true) {
            $coveragePercentage >= 90 => '1ccb3c',
            $coveragePercentage >= 80 => '6ccb1c',
            $coveragePercentage >= 70 => 'abcb1c',
            $coveragePercentage >= 60 => 'cbc31c',
            $coveragePercentage >= 50 => 'cb9b1c',
            $coveragePercentage >= 40 => 'cb891c',
            $coveragePercentage >= 30 => 'cb6c1c',
            $coveragePercentage >= 20 => 'cb4e1c',
            default => 'cb2f1c'
        };
        $readmeContent = file_get_contents($this->readmePath);
        $newCoverageBadge = sprintf(
            '<img src="https://img.shields.io/badge/Coverage-%s%%25-%s" alt="%s">',
            $coverageFormatted,
            $badgeColour,
            'Coverage ' . $coverageFormatted . '%'
        );
        $pattern = $this->getBasePattern('Coverage-.*?-[0-9a-fA-F]+', 'Coverage .*?');

        if (preg_match($pattern, $readmeContent)) {
            $readmeContent = preg_replace($pattern, $newCoverageBadge, $readmeContent);
            $successMessage = 'Coverage badge updated successfully.';
        } else {
            $badgeSection = "<!-- Coverage Badge -->\n$newCoverageBadge";
            $readmeContent = preg_replace(
                '/<!-- Coverage Badge -->/',
                $badgeSection,
                $readmeContent,
                1
            );
            $successMessage = 'Coverage badge added successfully.';
        }

        if (file_put_contents($this->readmePath, $readmeContent) === false) {
            $this->style->error('Unable to write to README.md file.');
            return Command::FAILURE;
        }

        $this->style->success($successMessage);

        return Command::SUCCESS;
    }
}