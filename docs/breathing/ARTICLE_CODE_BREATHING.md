# Code Breathing: Why Your Code Needs to Breathe

**TL;DR:** Cyclomatic complexity tells you *how many branches* a method has. Code breathing tells you *how long it takes to read*. Both matter — but we've been ignoring the second one.

---

## The reading problem

You've seen it. A method that "passes" all the rules: cyclomatic complexity under 10, no nesting deeper than 3, under 50 lines. And yet — it takes 15 minutes to understand.

```php
function calculateDiscount(User $u, Order $o, Item $i, Promo $p): float
{
    $d=0;if($u->vip()){$d=.2;}elseif($u->new()&&$o->total()>100){$d=.1;}
    elseif($i->cat()==='premium'&&$p->active()){$d=.15;}else{$d=0;}
    return $o->total()*$d;
}
```

Technically fine. Logically correct. **Unreadable.**

The problem isn't logic. It's **density**. No blank lines. Cramped identifiers. Everything squeezed. Your eyes have nowhere to rest.

---

## What is code breathing?

**Code breathing** is the visual rhythm of code — how easy it is to *scan* and *skim*, not just parse.

Articles say "5 min read" based on word count. Code has no equivalent. We measure branches (cyclomatic complexity), lines (LOC), nesting depth. We don't measure *how long it takes to read*.

Breathing metrics fix that. They measure:

| What | Why it matters |
|------|----------------|
| **Blank lines between blocks** | Visual pauses. Your brain needs them. |
| **Block length** | Short blocks (≤3 lines) are scannable. Long runs are exhausting. |
| **Identifier length** | `$d` vs `$discount` — one is cryptic, the other is clear. But `$firstOperandValueForCalculation` overloads. Sweet spot: 3–15 chars. |
| **Token density** | Keywords and operators (`if`, `&&`, `->`) cost more cognitive load than identifiers. Dense = heavy. |

---

## The science behind it

Reading code isn't like reading prose. You're pattern-matching, jumping between definitions, holding context in working memory. Cognitive load research (Sweller, Chandler) shows: **reducing extraneous load improves comprehension**.

Dense code = high extraneous load. Your brain spends energy on *finding* structure instead of *understanding* it.

Breathing metrics approximate this:

- **VBI (Visual Breathing Index)** — proportion of blank lines, block uniformity. Low = cramped.
- **COL (Code Oxygen Level)** — blank lines + short blocks per total lines. Low = suffocating.
- **IRS (Identifier Readability Score)** — average identifier length. Too short = cryptic. Too long = overloaded.
- **CBS (Code Breathing Score)** — combined score. 0.8+ = excellent. &lt;0.25 = very hard to read.

---

## Before and after

**Before (CBS ~0.2):**

```php
public function process(array $d): array
{
    $r=[];foreach($d as $i){if($i['s']==='a'){$r[]=$i['v'];}elseif($i['s']==='b'){$r[]=$i['v']*1.1;}else{$r[]=0;}}return $r;
}
```

**After (CBS ~0.75):**

```php
public function process(Collection $items): Collection
{
    return $items
        ->map(fn (Item $i) => $this->valueFor($i))
        ->values();
}

private function valueFor(Item $item): float
{
    return match ($item->status()) {
        'active' => $item->value(),
        'pending' => $item->value() * 1.1,
        default => 0.0,
    };
}
```

Same logic. Different rhythm. The second *breathes*.

---

## Why it matters for teams

1. **Onboarding** — New devs read 10× more code than they write. Dense code = slow onboarding.
2. **Code review** — Reviewers skim first. If code doesn't breathe, skimming fails. Reviews get shallow.
3. **AI-generated code** — LLMs produce correct but dense code. Breathing gates catch it.
4. **Refactoring** — "Make it work, make it right, make it fast" — breathing is part of "make it right."

---

## How to adopt it

1. **Measure** — Add breathing metrics to your quality gates (e.g. [Soda](https://github.com/bunnivo/soda)).
2. **Start lenient** — `min_code_breathing_score: 25` catches the worst. Tighten over time.
3. **Refactor hotspots** — Fix files with CBS &lt; 0.3 first. Biggest ROI.
4. **Educate** — One blank line between logical blocks. Short identifiers. Extract long blocks.

---

## The future

Cyclomatic complexity became standard in the 70s. Today we take it for granted. Code breathing is the next step — measuring not just *how complex* code is, but *how readable* it is.

Your code should breathe. Your team will thank you.

---

*Want to try it? [Soda](https://github.com/bunnivo/soda) — PHP quality gates with breathing metrics.*
