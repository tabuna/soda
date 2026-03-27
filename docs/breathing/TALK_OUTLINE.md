# Conference Talk: Code Breathing — The Metric We've Been Missing

**Duration:** 25–30 min  
**Level:** Intermediate  
**Audience:** PHP developers, tech leads, anyone caring about code quality

---

## Slide 1: Title

**Code Breathing: The Metric We've Been Missing**

*Why cyclomatic complexity isn't enough — and what to measure instead*

---

## Slide 2: The problem (1 min)

**"It passes all checks. Why is it still unreadable?"**

- Show dense one-liner that passes CC, LOC, nesting
- Poll: "How long to understand?" — usually 5–15 min
- Thesis: We measure *logic* complexity. We don't measure *reading* complexity.

---

## Slide 3: Reading vs. writing (2 min)

**We read 10× more than we write** (industry studies)

- Onboarding: reading
- Code review: reading
- Debugging: reading
- Refactoring: reading first

**Yet our metrics optimize for writing** — "is it correct?" not "is it scannable?"

---

## Slide 4: What is code breathing? (3 min)

**Definition:** The visual rhythm of code — how easy it is to *scan* and *skim*.

**Analogy:** Articles say "5 min read." Code has no equivalent. Until now.

**Four pillars:**
1. **Air** — blank lines between logical blocks
2. **Block size** — short blocks (≤3 lines) are scannable
3. **Identifier weight** — too short = cryptic, too long = overloaded
4. **Token density** — keywords/operators cost more than identifiers

---

## Slide 5: The metrics (4 min)

| Metric | Measures | Bad → Good |
|--------|----------|------------|
| **VBI** | Visual rhythm, blank lines | Cramped → Good rhythm |
| **COL** | Oxygen (blanks + short blocks) | Suffocating → Breathes well |
| **IRS** | Identifier length | Cryptic/Overloaded → Optimal |
| **CBS** | Combined score | &lt;0.25 → 0.8+ |

Quick formula overview (keep it light — "we've done the math").

---

## Slide 6: Live demo — before/after (5 min)

**Before:** Dense code, CBS ~0.2
```php
function p($d){$r=[];foreach($d as $i){if($i['a']>0){$r[]=$i['v']*$i['q'];}}return array_sum($r);}
```

**Run Soda** — violation: min_code_breathing_score

**After:** Same logic, refactored
```php
public function totalValue(Collection $items): float
{
    return $items
        ->filter(fn (Item $i) => $i->amount() > 0)
        ->map(fn (Item $i) => $i->value() * $i->quantity())
        ->sum();
}
```

**Run Soda** — passes.

**Key message:** Same cyclomatic complexity. Different reading time.

---

## Slide 7: Why now? AI-generated code (3 min)

**LLMs produce correct but dense code.**

- No blank lines (tokens cost money)
- Compact identifiers
- Long fluent chains without breaks

**Breathing gates catch it** — before it lands in your codebase.

---

## Slide 8: Adoption path (3 min)

1. **Add to CI** — `php soda quality src` (or similar)
2. **Start lenient** — мягкие пороги breathing/structural, потом ужесточать
3. **Fix hotspots** — refactor CBS &lt; 0.3 first
4. **Tighten over time** — 25 → 40 → 60 as team adapts

---

## Slide 9: Objections (2 min)

**"It's subjective"** — So was cyclomatic complexity. We quantified it. Same here.

**"It'll block our velocity"** — Start lenient. Fix incrementally. Velocity improves when reading is faster.

**"We have PHPStan"** — PHPStan finds bugs. Breathing finds unreadability. Different jobs.

---

## Slide 10: The future (1 min)

- Cyclomatic complexity: 1976 (McCabe)
- LOC: 1970s
- **Code breathing: 2025**

Next standard in code quality. Your code should breathe.

---

## Slide 11: Q&A

**Resources:**
- [Soda](https://github.com/bunnivo/soda) — PHP quality gates with breathing
- [Breathing Metrics docs](https://github.com/bunnivo/soda/blob/main/docs/BREATHING_METRICS.md)

---

## Speaker notes

- **Energy:** Start with frustration (dense code we've all seen), end with hope (actionable metric)
- **Demo:** Have a small repo ready. Show real `soda quality` output.
- **Poll:** "Who has seen code that passes all checks but is unreadable?" — expect 80%+ hands
- **Memorable line:** "Your code should breathe. Your team will thank you."
