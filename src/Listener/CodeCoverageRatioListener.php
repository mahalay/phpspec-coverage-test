<?php

namespace Mahalay\PhpSpec\CoverageTest\Listener;

use Mahalay\PhpSpec\CoverageTest\Exception\LowCoverageRatioException;
use PhpSpec\Event\SuiteEvent;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CodeCoverageRatioListener implements EventSubscriberInterface
{
    /**
     * @var CodeCoverage
     */
    private $coverage;

    /**
     * @var float
     */
    private $minimumCoverage;

    public function __construct(CodeCoverage $coverage, float $minimumCoverage)
    {
        $this->coverage = $coverage;
        $this->minimumCoverage = $minimumCoverage;
    }

    public function afterSuite(SuiteEvent $event): void
    {
        $actualCoverageRatio = $this->simplifyRatio($this->calculateRatio($this->coverage->getData()->lineCoverage()));

        if ($actualCoverageRatio < $this->minimumCoverage) {
            throw new LowCoverageRatioException(sprintf(
                'Test suites only cover %1.02f%% of the required %1.02f%% minimum coverage',
                $actualCoverageRatio,
                $this->minimumCoverage
            ));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'afterSuite' => ['afterSuite', -1000],
        ];
    }

    private function calculateRatio(array $coverageData): float
    {
        /** @var array<array-key, array|null> $lines */
        $lines = iterator_to_array($this->flattenLineCoverage($coverageData), false);
        $relevantLines = array_filter($lines, [$this, 'isRelevantLine']);

        return count(\array_filter($relevantLines)) / count($relevantLines);
    }

    private function flattenLineCoverage(array $lineCoverage): \Generator
    {
        if ($lineCoverage) {
            yield from array_shift($lineCoverage);
            yield from $this->flattenLineCoverage($lineCoverage);
        }
    }

    private function isRelevantLine(?array $line): bool
    {
        return !is_null($line);
    }

    /**
     * @param float $ratio
     *
     * @return float
     */
    private function simplifyRatio($ratio)
    {
        return (floor($ratio * 10000) / 10000) * 100;
    }
}
