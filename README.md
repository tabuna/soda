# Soda

## Introduction

Keep your PHP code **clean, maintainable, and architecturally sound** with Soda.  
Turn code metrics into enforceable quality rules—no god classes, no 500-line methods, no dependency sprawl.

AI writes faster than ever. Mistakes happen.
What if your code could **catch them first—before anyone else notices**? Soda quietly keeps your code consistent, safe, and review-ready.

> **Don't trust the prompt. Trust PHP Quality Gates!**


## Installation

Requires PHP 8.4 or higher.


> [!WARNING]
> Composer currently does not work. Use `git clone` as a temporary workaround.

```bash
composer require bunnivo/soda --dev
```


## Usage

### Analyse project metrics

Measure project size and structure. No configuration required.

```bash
php soda analyse src
```

### Quality checks

Create a configuration file:

```bash
php soda init
```

Or create `soda.json` manually:

```json
{
  "quality": {
    "min_score": 100
  },
  "rules": {
    "max_method_length": 100,
    "max_class_length": 800,
    "max_arguments": 3,
    "max_methods_per_class": 40,
    "max_file_loc": 1000,
    "max_cyclomatic_complexity": 15,
    "max_control_nesting": 3,
    "max_properties_per_class": 6,
    "max_public_methods": 40,
    "max_dependencies": 8,
    "max_classes_per_file": 1,
    "max_namespace_depth": 4,
    "max_classes_per_namespace": 16,
    "max_traits_per_class": 100,
    "max_interfaces_per_class": 100,
    "max_classes_per_project": 2000,
    "min_code_breathing_score": 25,
    "min_visual_breathing_index": 10,
    "min_identifier_readability_score": 70,
    "min_code_oxygen_level": 25,
    "max_weighted_cognitive_density": 40,
    "max_logical_complexity_factor": 50
  }
}
```

Run the quality check:

```bash
php soda quality src
```

Example output:

```
Code Quality Report
──────────────────

Score: 82 / 100

Violations
──────────

❌ App\Services\UserService
   Properties per class: 18 (max 15)

⚠️ App\Controllers\UserController
   Methods per class: 22 (max 20)

Summary
───────

Violations: 2
Score: 82
```

**Exit code:** 0 when score ≥ min_score, 1 otherwise.

### Options

| Option           | Description                   |
|------------------|-------------------------------|
| `--config=`      | Path to config file           |
| `--report-json=` | Export report to JSON         |
| `--suffix=`      | File suffix (default: `.php`) |
| `--exclude=`     | Exclude paths (repeatable)    |

## soda.json Reference

`soda.json` has two sections: `quality` (pass threshold) and `rules` (per-metric limits).

### quality.min_score

Minimum overall score (1–100). Exit code 1 when score &lt; min_score. See [How Score is calculated](docs/QUALITY_SCORE.md).

### rules — Metrics by Type

| Type | Rules | Docs |
|------|-------|------|
| **Structural** | `max_method_length`, `max_class_length`, `max_file_loc`, `max_arguments`, `max_dependencies`, `max_properties_per_class`, `max_public_methods`, `max_methods_per_class`, `max_classes_per_file`, `max_namespace_depth`, `max_classes_per_namespace`, `max_traits_per_class`, `max_interfaces_per_class`, `max_classes_per_project`, `max_return_statements`, `max_boolean_conditions` | [Structural Metrics](docs/STRUCTURAL_METRICS.md) |
| **Complexity / Readability** | `max_cyclomatic_complexity`, `max_control_nesting`, `min_code_breathing_score`, `min_visual_breathing_index`, `min_identifier_readability_score`, `min_code_oxygen_level`, `max_weighted_cognitive_density`, `max_logical_complexity_factor` | [Complexity & Readability](docs/COMPLEXITY_READABILITY_METRICS.md) |

Each doc includes **good/bad examples** (in English), **possible values**, and **breathing metric ranges**.

### Disabling rules

Set limit to `0` to disable: `"max_control_nesting": 0`, `"min_code_breathing_score": 0`.

### Defaults

Rules not in `soda.json` use `QualityConfig::DEFAULT_RULES`. Run `php soda init` to create a full config with all rules.

### Documentation

| Document | Content |
|----------|---------|
| [Structural Metrics](docs/STRUCTURAL_METRICS.md) | Size, dependencies, structure — good/bad examples, possible values |
| [Complexity & Readability](docs/COMPLEXITY_READABILITY_METRICS.md) | Cyclomatic complexity, nesting, breathing metrics — examples, config ranges |
| [Quality Score](docs/QUALITY_SCORE.md) | How score is calculated, penalties, min_score gate |
| [Breathing Metrics](docs/BREATHING_METRICS.md) | Breathing metrics overview |

## CI Integration

**Run Soda after** linters and static analyzers. It checks structure and readability — not syntax or types.

Recommended order:

1. **Laravel Pint** (or similar) — formatting
2. **Rector** — refactoring
3. **PHPStan** (or Psalm) — static analysis
4. **Soda** — quality gates (structure, complexity, breathing)

```yaml
- run: composer install --no-interaction
- run: ./vendor/bin/pint
- run: ./vendor/bin/rector process --no-progress-bar
- run: ./vendor/bin/phpstan analyse
- run: php soda quality src
```

Minimal (Soda only):

```yaml
- run: composer install --no-interaction
- run: php soda quality src
```

## Contributing

Thank you for considering contributing to Soda! Please feel free to submit a Pull Request.

## License

Soda is open-sourced software licensed under the [BSD 3-Clause License](LICENSE).
