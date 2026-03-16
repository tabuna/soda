# Soda

Write PHP code that **stays clean, maintainable, and architecturally solid**.

Soda helps you **enforce quality rules automatically**, so you don’t have to hunt for hidden issues in huge classes or 500-line methods. Turn code metrics into actionable rules that make sense for your project.

Even with AI-generated code, errors slip through. **Soda catches them first**, keeping your code consistent, safe, and review-ready—before it ever reaches a pull request.

> **Stop trusting guesses. Start trusting PHP Quality Gates.**

## Installation

Requires PHP 8.4 or higher.


> [!WARNING]
> Composer currently does not work. Use `git clone` as a temporary workaround.

```bash
composer require bunnivo/soda --dev
```

## Usage

### Analyze project metrics

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
    "structural": {
      "max_method_length": 100,
      "max_class_length": 800,
      "max_arguments": 3,
      "max_methods_per_class": 40,
      "max_file_loc": 1000,
      "max_properties_per_class": 6,
      "max_public_methods": 40,
      "max_dependencies": 8,
      "max_classes_per_file": 1,
      "max_namespace_depth": 4,
      "max_classes_per_namespace": 16,
      "max_traits_per_class": 100,
      "max_interfaces_per_class": 100,
      "max_classes_per_project": 2000
    },
    "complexity": {
      "max_cyclomatic_complexity": 15,
      "max_control_nesting": 3,
      "max_weighted_cognitive_density": 40,
      "max_logical_complexity_factor": 50,
      "max_return_statements": 4,
      "max_boolean_conditions": 3
    },
    "breathing": {
      "min_code_breathing_score": 25,
      "min_visual_breathing_index": 10,
      "min_identifier_readability_score": 70,
      "min_code_oxygen_level": 25
    }
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

### Disabling rules

Set limit to `0` to disable: `"max_control_nesting": 0`, `"min_code_breathing_score": 0`.

### quality.min_score

Minimum overall score (1–100). Exit code 1 when score &lt; min_score.
See [How Score is calculated](docs/QUALITY_SCORE.md).

### Documentation

| Document                                                           | Content                                                                     |
|--------------------------------------------------------------------|-----------------------------------------------------------------------------|
| [Structural Metrics](docs/STRUCTURAL_METRICS.md)                   | Size, dependencies, structure — good/bad examples, possible values          |
| [Complexity & Readability](docs/COMPLEXITY_READABILITY_METRICS.md) | Cyclomatic complexity, nesting, breathing metrics — examples, config ranges |
| [Quality Score](docs/QUALITY_SCORE.md)                             | How score is calculated, penalties, min_score gate                          |
| [Breathing Metrics](docs/BREATHING_METRICS.md)                     | Breathing metrics overview                                                  |

## CI Integration

**Run Soda after** linters and static analyzers. It checks structure and readability — not syntax or types.

Recommended order:

1. **Rector** — refactoring
2. **PHPStan** (or Psalm) — static analysis
3. **Laravel Pint** (or similar) — formatting
4. **Soda** — quality gates (structure, complexity, breathing)

```yaml
- name: Install composer dependencies
  run: composer install --no-interaction

- name: Run other tools
  run: echo "This is a placeholder for other tools"

- name: Run Soda quality check
  run: php soda quality src
```

## Contributing

Thank you for considering contributing to Soda! Please feel free to submit a Pull Request.

## License

Soda is open-sourced software licensed under the [BSD 3-Clause License](LICENSE).
