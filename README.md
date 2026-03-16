# Soda

## Introduction

Keep your PHP code **clean, maintainable, and architecturally sound** with Soda.  
Turn code metrics into enforceable quality rules—no god classes, no 500-line methods, no dependency sprawl.


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
    "min_score": 80
  },
  "rules": {
    "max_method_length": 120,
    "max_class_length": 500,
    "max_arguments": 5,
    "max_methods_per_class": 20,
    "max_file_loc": 400,
    "max_cyclomatic_complexity": 10,
    "max_properties_per_class": 15,
    "max_public_methods": 15,
    "max_dependencies": 12,
    "max_classes_per_file": 1,
    "max_namespace_depth": 4,
    "max_classes_per_namespace": 40,
    "max_traits_per_class": 3,
    "max_interfaces_per_class": 5,
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

Exit code is 0 when score ≥ min_score, 1 otherwise.

### Options

| Option           | Description                   |
|------------------|-------------------------------|
| `--config=`      | Path to config file           |
| `--report-json=` | Export report to JSON         |
| `--suffix=`      | File suffix (default: `.php`) |
| `--exclude=`     | Exclude paths (repeatable)    |

## Supported Rules

| Rule                        | Description                          |
|-----------------------------|--------------------------------------|
| `max_method_length`         | Max lines per method                 |
| `max_class_length`          | Max lines per class                  |
| `max_arguments`             | Max method arguments                 |
| `max_methods_per_class`     | Max methods per class                |
| `max_file_loc`              | Max lines per file                   |
| `max_cyclomatic_complexity` | Max cyclomatic complexity per method |
| `max_properties_per_class`  | Max properties per class             |
| `max_public_methods`        | Max public methods per class         |
| `max_dependencies`          | Max constructor parameters           |
| `max_classes_per_file`      | Max classes per file                 |
| `max_namespace_depth`       | Max namespace depth                  |
| `max_classes_per_namespace` | Max classes per namespace            |
| `max_traits_per_class`      | Max traits per class                 |
| `max_interfaces_per_class`  | Max interfaces per class             |
| `max_classes_per_project`   | Max classes in project               |
| `min_code_breathing_score`  | Min Code Breathing Score             |
| `min_visual_breathing_index`| Min Visual Breathing Index           |
| `min_identifier_readability_score` | Min Identifier Readability Score |
| `min_code_oxygen_level`     | Min Code Oxygen Level                |
| `max_weighted_cognitive_density`   | Max Weighted Cognitive Density |
| `max_logical_complexity_factor`    | Max Logical Complexity Factor  |


## CI Integration

```yaml
- run: composer install --no-interaction
- run: php soda quality src
```

## Contributing

Thank you for considering contributing to Soda! Please feel free to submit a Pull Request.

## License

Soda is open-sourced software licensed under the [BSD 3-Clause License](LICENSE).
