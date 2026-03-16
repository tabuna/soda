# Breathing Metrics

Breathing metrics measure how **easy code is to read and scan visually** — the "rhythm" of code, not just its logic.

---

## The "5 minutes to read" idea

Many articles show: *"Reading this will take about 5 minutes."* That estimate comes from word count and average reading speed — and it works.

**What if we applied the same idea to code?**

Breathing metrics estimate how long it takes to *scan and understand* code — by measuring line density, block length, and identifier weight. Dense code without visual pauses is harder to skim.

---

## Visual example: dense vs airy

Compare two blocks. The first is dense — long runs without blank lines:

```
XXXXXXXXXXXXXXXXXXX XX XX XX XXXXXXXX XXX XX XXXXX XXX
    XXXXX XX XXXX

    XXXXXXX XXXXXXXX XXXXXXX XXXXX
        XXXXXX XXXXXXXX XXXXX XX XXXXXXX XXXXX XXXXX XXX
            XX XXXXX XXXXXXXX XXX
                XXXXXXXXXXXXXXXXXXXXXXXX
                XXXXXXX XX XXXXXXXXXX XXXXXXXXX
                    X XXXXX XXXXX
                    X XXXXX XXXX
            XX XXXX
                XXXXXXX XX XXXXX
            XX
        XX
    XX

    XXXXX XXXXXXX
XX
```

The second has more "air" — shorter blocks, clearer rhythm:

```
XXXXXXXX XXXXX XXX

    XX XXXXX XXXXXXXX XXX
        XXXXX XX XXXX
    XX

    XXXXX XXXXXXX
XX
```

The second is easier to scan, even with less text.

---

## Identifier length matters

Sometimes code looks compact but reads heavy because of **long identifiers**:

```php
// ✅ Easier to scan
function add(int $a, int $b): int {
    return $a + $b;
}
```

```php
// ❌ Heavier — long names overload perception
function addTwoNumbersWithMeaningfulNames(
    int $firstOperandValue,
    int $secondOperandValue
): int {
    return $firstOperandValue + $secondOperandValue;
}
```

Both do the same thing, but the second is harder to skim — long identifiers scatter attention and reduce "breathing."

---

## What is "code breathing"?

Code has a **reading rhythm**. Dense blocks without pauses are hard to scan. Short identifiers, blank lines between logical blocks, and moderate nesting make code "breathe" — easier to skim and understand.

**Easy to read:**
- Blank lines between logical blocks
- Short blocks (≤3 lines)
- Identifiers of reasonable length (3–15 chars)

**Hard to read:**
- Long blocks without pauses
- Dense, compact code
- Very long identifiers

---

## Metrics overview

| Metric | What it measures | Config key |
|--------|------------------|------------|
| **CBS** (Code Breathing Score) | Overall readability (0–1) | `min_code_breathing_score` |
| **VBI** (Visual Breathing Index) | Proportion of blank lines, block uniformity | `min_visual_breathing_index` |
| **IRS** (Identifier Readability Score) | Average identifier length | `min_identifier_readability_score` |
| **COL** (Code Oxygen Level) | Blank lines + short blocks | `min_code_oxygen_level` |
| **WCD** (Weighted Cognitive Density) | Token density (keywords, operators weighted higher) | `max_weighted_cognitive_density` |
| **LCF** (Logical Complexity Factor) | Conditions, loops, nesting depth | `max_logical_complexity_factor` |

---

## Formulas

### WCD (Weighted Cognitive Density)

Token density, with weights by type:

| Token type      | Weight |
|-----------------|--------|
| identifier      | 1.0    |
| keyword         | 1.2    |
| operator        | 1.5    |
| logical (`&&`, `\|\|`) | 2.0 |
| function call   | 1.3    |
| literal         | 0.8    |
| delimiter       | 0.5    |

$$
\text{WCD} = \frac{\sum_{i=1}^{N} \bigl(\text{length}(token_i) \cdot \text{weight}(token_i)\bigr)}{N_{\text{lines}}}
$$

| WCD   | Interpretation |
|-------|-----------------|
| &lt;10  | Light           |
| 10–20 | Normal          |
| 20–35 | Dense           |
| &gt;35 | Overloaded      |

### LCF (Logical Complexity Factor)

Flow complexity: conditions, loops, nesting.

$$
\text{LCF} = 1 + 0.3 \cdot N_{\text{cond}} + 0.2 \cdot N_{\text{loop}} + 0.4 \cdot depth_{\max}
$$

### VBI (Visual Breathing Index)

Proportion of blank lines and block uniformity.

$$
\text{VBI} = \frac{N_{\text{blank}}}{N_{\text{lines}}} \times \left(1 - \frac{\sigma_{\text{block}}}{max_{\text{block}}}\right)
$$

| VBI   | Interpretation |
|-------|-----------------|
| 0–0.2 | Cramped         |
| 0.2–0.4 | Normal       |
| 0.4–0.6 | Good rhythm  |

### IRS (Identifier Readability Score)

$$
\text{IRS} = 1 - \frac{\text{avgIdentifierLength} - 8}{20}
$$

| Length | Readability |
|--------|-------------|
| 3–8    | Optimal     |
| 8–15   | Acceptable  |
| &gt;15  | Overloaded   |

### COL (Code Oxygen Level)

$$
\text{COL} = \frac{N_{\text{blank}} + N_{\text{shortBlocks}}}{N_{\text{lines}}}
$$

Short blocks = blocks ≤3 lines.

| COL   | Interpretation |
|-------|----------------|
| &lt;0.1 | Suffocating   |
| 0.1–0.3 | Normal      |
| &gt;0.3 | Breathes well |

### CBS (Code Breathing Score)

$$
\text{CBS} = \min\left(1,\; \frac{(VBI \times IRS \times COL) \times \text{sizeFactor}}{1 + \frac{WCD \times \min(LCF, 4)}{\text{divisor}}}\right)
$$

Size factor and divisor depend on file length. Array/fluent blocks get special handling.

| CBS     | Interpretation |
|---------|----------------|
| 0.8–1.0 | Excellent      |
| 0.6–0.8 | Good           |
| 0.4–0.6 | Dense          |
| &lt;0.4  | Hard to read   |

---

## CBS ranges (config 0–100, internal 0–1)

| Config | Internal | Interpretation |
|--------|----------|----------------|
| 80–100 | 0.80–1.0 | Excellent |
| 60–80 | 0.60–0.80 | Good |
| 40–60 | 0.40–0.60 | Dense |
| 25–40 | 0.25–0.40 | Hard to read |
| &lt;25 | &lt;0.25 | Very hard |

---

## Configuration in soda.json

Config uses 0–100 scale for min_* (internal 0–1). Set to `0` to disable.

| Rule | Default | Scale |
|------|---------|-------|
| `min_code_breathing_score` | 0 (off) | 0–100 (25 = 0.25) |
| `min_visual_breathing_index` | 12 | 0–100 |
| `min_identifier_readability_score` | 75 | 0–100 |
| `min_code_oxygen_level` | 25 | 0–100 |
| `max_weighted_cognitive_density` | 30 | Raw (0–50+) |
| `max_logical_complexity_factor` | 35 | Config = internal×10 |

See [COMPLEXITY_READABILITY_METRICS.md](COMPLEXITY_READABILITY_METRICS.md) for good/bad examples and config ranges.
