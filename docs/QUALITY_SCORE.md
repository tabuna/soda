# Quality Score

How the overall quality score is calculated and what `min_score` means. Source: `QualityEngine::calculateScore()`.

---

## Formula

```
Score = max(0, 100 - sum(penalties))
```

- **Start:** 100
- **Each violation** subtracts a penalty (2 or 3 points)
- **Result** is clamped to 0–100

---

## Penalties per Rule

| Rule | Penalty |
|------|---------|
| `max_class_length` | 3 |
| `max_cyclomatic_complexity` | 3 |
| `max_classes_per_project` | 3 |
| All other rules | 2 |

---

## Examples

| Violations | Penalty sum | Score |
|------------|-------------|-------|
| 0 | 0 | 100 |
| 1 (max_method_length) | 2 | 98 |
| 2 (max_arguments, max_dependencies) | 4 | 96 |
| 5 (mixed) | 10 | 90 |
| 10 | 20 | 80 |
| 50 | 100 | 0 |

---

## min_score Gate

`quality.min_score` in `soda.json` is the **pass threshold**:

- **Exit code 0** (success) when `Score ≥ min_score`
- **Exit code 1** (failure) when `Score < min_score`

| min_score | Effect |
|-----------|--------|
| 100 | Only perfect code passes |
| 80 | Common CI gate |
| 50 | Lenient |
| 1 | Almost everything passes |

---

## Relation to Violations

- More violations → lower score
- Heavier rules (class length, cyclomatic complexity, project size) cost 3 points
- Lighter rules cost 2 points
- Score does **not** depend on *how much* a rule is exceeded, only on *whether* it was violated
