# Complexity / Readability / Perceptibility Metrics

Metrics that measure **cognitive load**, **control flow complexity**, and **visual readability** of code.

**Config:** Complexity rules (`max_cyclomatic_complexity`, `max_control_nesting`, WCD, LCF, etc.) live under `rules.complexity`; breathing rules under `rules.breathing`. Defaults: `QualityConfig::DEFAULT_RULES`. Breathing metrics use config scale 0–100 for min_* (internal 0–1); WCD/LCF use raw/scaled values.

### max_cyclomatic_complexity

Counts branching: `if`, `elseif`, `switch`, `case`, `for`, `foreach`, `while`, `catch`, `&&`, `||`, `?:`.

| Config value | Strictness | Interpretation |
|--------------|------------|----------------|
| 5–8 | Strict | Simple methods only |
| 10–15 | Moderate | Acceptable |
| 20+ | Lenient | Complex logic allowed |

```php
// ❌ Bad (complexity 20+)
public function calculateDiscount(User $user, Order $order): float
{
    if ($user->isPremium()) {
        if ($order->total() > 1000) {
            return 0.2;
        }
        return 0.1;
    }
    if ($user->isNew()) {
        if ($order->items()->count() > 5) {
            return 0.05;
        }
        return 0;
    }
    if ($order->total() > 500 && $user->hasCoupon()) {
        return 0.15;
    }
    // ... 10 more branches
}
```

```php
// ✅ Good
public function calculateDiscount(User $user, Order $order): float
{
    return $this->strategies
        ->findFor($user)
        ->discount($order);
}
```

---

## Nesting Depth

### max_control_nesting

Maximum nesting level of `if`, `for`, `foreach`, `while`, `switch`.

| Config value | Strictness |
|--------------|------------|
| 2–3 | Strict |
| 4 | Moderate |
| 5+ | Lenient |

```php
// ❌ Bad (4+ levels)
if ($a) {
    foreach ($b as $x) {
        if ($x->valid()) {
            switch ($x->type()) {
                case 1:
                    if ($y) {
                        // ...
                    }
                    break;
            }
        }
    }
}
```

```php
// ✅ Good
foreach ($this->filterValid($items) as $item) {
    $this->handle($item);
}
```

---

### max_try_catch_blocks (excessive exception handling)

**What it counts:** number of `try { … } catch (…) { … }` statements in a method or top-level function (each `try` counts once; `finally` does not add a second count). Nested `try` inside the same method counts separately. `try` inside closures passed to `array_map` and similar is **not** attributed to the enclosing method.

| Config value | Severity (default labels) | Meaning |
|--------------|---------------------------|---------|
| `2` | Warning at ≥3 blocks | Default: allow up to two `try/catch` per method |
| `0` | Disabled | Rule off |

```php
// ❌ Bad (3+ try/catch in one method when max_try_catch_blocks is 2)
function run() {
    try {} catch (\Exception $e) {}
    try {} catch (\Exception $e) {}
    try {} catch (\Exception $e) {}
}
```

```php
// ✅ Good: consolidate error handling or extract helpers with a single try/catch boundary
function run(): void
{
    try {
        $this->stepOne();
        $this->stepTwo();
    } catch (ProcessException $e) {
        $this->recover($e);
    }
}
```

---

## Cognitive Complexity (Breathing Metrics)

These metrics measure how hard code is to **read and scan visually**. Config values use a **0–100 scale** where the internal metric is scaled (e.g. CBS 0.25 → config 25).

---

### min_code_breathing_score (CBS)

**Integral readability score.** Combines VBI, IRS, COL, WCD, LCF.

| Config value | Internal (0–1) | Interpretation |
|--------------|----------------|----------------|
| 80–100 | 0.80–1.0 | Excellent |
| 60–80 | 0.60–0.80 | Good |
| 40–60 | 0.40–0.60 | Dense |
| 25–40 | 0.25–0.40 | Hard to read |
| &lt;25 | &lt;0.25 | Very hard |

**Set to `0` to disable.**

```php
// ❌ Bad (CBS < 25)
function process($d){$r=[];foreach($d as $i){if($i['a']>0){$r[]=$i['v']*$i['q'];}}return array_sum($r);}
```

```php
// ✅ Good
public function totalValue(Collection $items): float
{
    return $items
        ->filter(fn (Item $i) => $i->amount() > 0)
        ->map(fn (Item $i) => $i->value() * $i->quantity())
        ->sum();
}
```

---

### min_visual_breathing_index (VBI)

**Visual rhythm:** proportion of blank lines and block size uniformity.

| Config value | Internal (0–1) | Interpretation |
|--------------|-----------------|----------------|
| 40–60 | 0.40–0.60 | Good rhythm |
| 20–40 | 0.20–0.40 | Normal |
| 10–20 | 0.10–0.20 | Tight |
| &lt;10 | &lt;0.10 | Cramped |

**Set to `0` to disable.**

```php
// ❌ Bad (VBI < 10)
function a(){return 1;}
function b(){return 2;}
function c(){return 3;}
// ... 50 functions in a row, no blank lines
```

```php
// ✅ Good
function a(): int
{
    return 1;
}

function b(): int
{
    return 2;
}
```

---

### min_identifier_readability_score (IRS)

**Readability of names.** Based on average identifier length.

| Config value | Internal (0–1) | Interpretation |
|--------------|-----------------|----------------|
| 75–100 | 0.75–1.0 | Optimal (3–8 chars) |
| 70–75 | 0.70–0.75 | Acceptable (8–15 chars) |
| &lt;70 | &lt;0.70 | Overloaded (&gt;15 chars) |

**Set to `0` to disable.**

```php
// ❌ Bad (IRS < 70)
$d = $u->getD();
$t = $o->getT();
$q = $i->getQ();
```

```php
// ✅ Good
$discount = $user->discount();
$total = $order->total();
$quantity = $item->quantity();
```

---

### min_code_oxygen_level (COL)

**Visual "air" in code:** blank lines + short blocks (≤3 lines).

| Config value | Internal (0–1) | Interpretation |
|--------------|-----------------|----------------|
| 30–65 | 0.30–0.65 | Breathes well |
| 10–30 | 0.10–0.30 | Normal |
| &lt;10 | &lt;0.10 | Suffocating |

**Set to `0` to disable.**

```php
// ❌ Bad (COL < 25)
if($x){$a=1;$b=2;$c=3;$d=4;$e=5;$f=6;}
foreach($items as $i){$r[]=$i*2;}
return $r;
```

```php
// ✅ Good
if ($condition)
{
    $a = 1;
    $b = 2;
}

foreach ($items as $item)
{
    $result[] = $item * 2;
}

return $result;
```

---

### max_weighted_cognitive_density (WCD)

**Text density.** Token weights: keywords 1.2, operators 1.5, logical 2.0.

| Config value | Interpretation |
|--------------|----------------|
| &lt;10 | Light code |
| 10–20 | Normal |
| 20–35 | Dense |
| 30–40 | Config default |
| &gt;40 | Overloaded |

**Set to `0` to disable.**

```php
// ❌ Bad (WCD > 40)
return $a&&$b||$c?$x:$y&&$z?$p:$q;
```

```php
// ✅ Good
if ($a && $b || $c) {
    return $x;
}
return $y && $z ? $p : $q;
```

---

### max_logical_complexity_factor (LCF)

**Flow complexity:** conditions, loops, nesting depth. Config stores value × 10 (e.g. 35 → 3.5).

| Config value | Internal | Interpretation |
|--------------|----------|----------------|
| 20–35 | 2.0–3.5 | Normal |
| 35–50 | 3.5–5.0 | Config default |
| &gt;50 | &gt;5.0 | Complex flow |

**Set to `0` to disable.**

```php
// ❌ Bad (LCF > 50)
if ($a) {
    foreach ($b as $x) {
        if ($x->valid()) {
            switch ($x->type()) {
                case 1: if ($y) { ... } break;
                case 2: while ($z) { ... } break;
            }
        }
    }
}
```

```php
// ✅ Good
foreach ($this->filterValid($items) as $item) {
    $this->handle($item);
}
```

---

## Summary: Breathing config values

| Rule | Section | Config range | Disable |
|------|---------|--------------|---------|
| `min_code_breathing_score` | `breathing` | 25–80 | `0` |
| `min_visual_breathing_index` | `breathing` | 10–40 | `0` |
| `min_identifier_readability_score` | `breathing` | 70–100 | `0` |
| `min_code_oxygen_level` | `breathing` | 25–65 | `0` |
| `max_weighted_cognitive_density` | `complexity` | 10–40 | `0` |
| `max_logical_complexity_factor` | `complexity` | 20–50 | `0` |
| `max_try_catch_blocks` | `complexity` | 2 (warn at 3+) | `0` |

See [BREATHING_METRICS.md](BREATHING_METRICS.md) for overview.
