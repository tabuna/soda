<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality;

use Bunnivo\Soda\Quality\Engine\EvaluateInput;
use Bunnivo\Soda\Quality\Engine\QualityEngineEvaluatePipeline;
use Bunnivo\Soda\Quality\Rule\RuleChecker;
use Bunnivo\Soda\Quality\RuleRegistry\RuleRegistry;
use Bunnivo\Soda\Result;

final readonly class QualityEngine
{
    /**
     * @param list<RuleChecker> $checkers
     */
    public function __construct(
        private QualityConfig $config,
        private array $checkers,
    ) {}

    public function evaluate(Result $metrics, EvaluateInput $input): QualityResult
    {
        return QualityEngineEvaluatePipeline::finish([
            'config'   => $this->config,
            'checkers' => $this->checkers,
            'metrics'  => $metrics,
            'input'    => $input,
        ]);
    }

    /**
     * @param list<RuleChecker> $extraCheckers Appended after built-in checkers (from the app's config/soda.php when present).
     */
    public static function create(QualityConfig $config, array $extraCheckers = []): self
    {
        $checkers = [...RuleRegistry::default($config), ...$extraCheckers];

        return new self($config, $checkers);
    }
}
