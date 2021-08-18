<?php

namespace Mahalay\PhpSpec\CoverageTest\Listener;

use Mahalay\PhpSpec\CoverageTest\Exception\LowCoverageRatioException;
use PhpSpec\Event\SuiteEvent;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CodeCoverageRatioListener implements EventSubscriberInterface
{
    /**
     * @var CodeCoverage
     */
    private $coverage;

    /**
     * @var float
     */
    private $minimumCoverage;

    /**
     * CodeCoverageRatioListener constructor.
     *
     * @param float $minimumCoverage
     */
    public function __construct(CodeCoverage $coverage, $minimumCoverage)
    {
        $this->coverage = $coverage;
        $this->minimumCoverage = (float) $minimumCoverage;
    }

    public function afterSuite(SuiteEvent $event): void
    {
        $actualCoverageRatio = $this->simplifyRatio($this->calculateRatio($this->coverage->getData()));

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

    /**
     * @return float
     */
    private function calculateRatio(array $coverageData)
    {
        $lines = iterator_to_array($this->flattenLineCoverage($coverageData), false);

        return count(array_filter($lines)) / count($lines);
    }

    /**
     * @param array<string, array> $lineCoverage
     */
    private function flattenLineCoverage($lineCoverage): \Generator
    {
        if ($lineCoverage) {
            yield from array_shift($lineCoverage);
            yield from $this->flattenLineCoverage($lineCoverage);
        }
    }

    /**
     * @param float $ratio
     *
     * @return float
     */
    private function simplifyRatio($ratio)
    {
        return (ceil($ratio * 10000) / 10000) * 100;
    }
}
