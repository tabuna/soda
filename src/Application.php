<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\QualityAnalyser;
use Bunnivo\Soda\Quality\QualityAnalysisContract;
use Illuminate\Container\Container;

final class Application extends Container
{
    public function __construct()
    {
        $this->singleton(
            QualityAnalysisContract::class,
            static fn (): QualityAnalyser => new QualityAnalyser,
        );
    }

    public function runningUnitTests(): bool
    {
        return ($_ENV['env'] ?? '') === 'testing';
    }
}
