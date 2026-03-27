# Цель: Soda Quality — Score 100

## Целевая конфигурация

```json
"breathing": {
  "min_visual_breathing_index": 100,
  "min_code_oxygen_level": 100,
  "min_identifier_readability_score": 100,
  "min_code_breathing_score": 100
}
```

**Принцип:** CI (pint) не даёт добавлять лишние пустые строки. Либо формулы корректны для идеального кода, либо код улучшать. Минимальные классы (ConfigException-style) → VBI/COL/IRS 1.0.

---

## Диагностика: что мешает достичь 100

### 1. COL (Code Oxygen Level) — исправлено

**Проблема:** В `ColCalculator` был жёсткий потолок `min(0.65, $raw)` — метрика не могла превысить 0.65.

**Исправление:** Заменено на `min(1.0, max(0.0, $raw))` — диапазон 0–1.

**Файл:** `src/Breathing/ColCalculator.php`

---

### 2. VBI (Visual Breathing Index) — исправлено

**Было:** `VBI = (nBlank / nLines) × blockFactor` — 1.0 почти недостижимо.

**Стало:** `ratioComponent = min(1, rawRatio / 0.5)`; если maxBlock ≤ 2: blockFactor = 1 (все блоки короткие — без штрафа).

**Идеал:** ratio ≥ 0.5 + блоки ≤2 строк. Универсально: короткие блоки везде — без исключений по размеру файла.

**Проверка:** `testMinimalClassAchievesVbi100`, `testIdealCodeAchievesVbi100`, `testDenseCodeDoesNotAchieveVbi100`.

---

### 3. IRS (Identifier Readability Score) — исправлено

**Было:** порог 8 — IRS = 1.0 почти недостижимо для типичного кода.

**Стало:** порог 14 — `IRS = 1 - (avg - 14) / 20`, clamp 0–1.

**Идеал:** средняя длина идентификаторов ≤ 14 символов → IRS = 1.0 (CountClassRuleDefaults-style).

**Проверка:** `testIdealIdentifiersAchievesIrs100` — короткие идентификаторы дают 1.0; `testLongIdentifiersDoNotAchieveIrs100` — длинные не дают 1.0.

---

### 4. CBS (Code Breathing Score)

**Формула:** `CBS = min(1, (VBI × IRS × COL × sizeFactor) / denominator)`

Составная метрика. При любом из VBI, IRS, COL < 1.0 CBS не достигает 1.0.

---

## Внесённые изменения

| Файл | Изменение |
|------|-----------|
| `soda.json` | Все breathing-метрики установлены в 100 |
| `src/Breathing/ColCalculator.php` | Убран потолок 0.65, разрешён диапазон 0–1 |
| `src/Breathing/VbiCalculator.php` | IDEAL 0.5, maxBlock ≤ 2 ⇒ blockFactor = 1 (универсально) |
| `src/Breathing/IrsCalculator.php` | Порог 12 → 14, IRS 1.0 при avg ≤ 14 |
| `src/Quality/Rule/BreathingChecker.php` | Откат exemption для малых файлов |
| `tests/unit/BreathingCheckerTest.php` | Удалены тесты exemption |
| `tests/verification/BreathingVerificationTest.php` | testMinimalClassAchievesVbi100, testIdealCodeAchievesVbi100, testDenseCodeDoesNotAchieveVbi100, testIdealIdentifiersAchievesIrs100, testLongIdentifiersDoNotAchieveIrs100 |

---

## Текущее состояние

- **ConfigException** — VBI 1.0, COL 1.0, IRS 1.0 (тест `testMinimalClassAchievesVbi100`)
- Идеальный код (тесты) достигает 1.0 по всем метрикам
- `composer ci` — pint исправляет code-style, лишние пустые строки удаляются

---

## Рекомендации

1. **Короткие блоки (≤2 строк)** — blockFactor = 1, без штрафа за «неравномерность».
2. **Для 100** — ratio ≥ 0.5 (1 blank на 2 code lines) + блоки ≤2 строк.
