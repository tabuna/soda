# Сравнительный анализ Soda и PHPMD

**Версия:** 1.0  
**Дата:** 2025-03-17  
**Цель:** Беспристрастное сравнение инструментов анализа PHP-кода для выбора при внедрении в стек разработки.

---

## 1. Краткое резюме

| Критерий | Soda | PHPMD |
|----------|------|-------|
| **Фокус** | Метрики качества, структура, «дышащий» код | Code smells, потенциальные баги, неиспользуемый код |
| **Подход** | Пороговые значения (max/min), отчёт по нарушениям | Правила по категориям (rulesets) |
| **Уникальное** | Breathing metrics, структурные лимиты | Unused code, naming, design rules |
| **PHP** | ≥8.3 | ≥5.3.9 |
| **Зависимости** | nikic/php-parser, sebastian/*, Laravel | pdepend/pdepend |

---

## 2. Функциональный анализ

### 2.1 Soda — возможности

| Категория | Возможности |
|-----------|-------------|
| **Анализ метрик** | `php soda analyse src` — размер, структура, зависимости |
| **Quality gates** | `php soda quality src` — проверка правил, exit code 0/1 |
| **Конфигурация** | `soda.json` — пороги по секциям (structural, complexity, breathing) |
| **Отчёты** | CLI (текст), `--report-json=` |
| **Расширение** | Только через изменение порогов; кастомные правила — нет |

**Правила Soda (27 шт.):**

- **Structural (15):** max_method_length, max_class_length, max_arguments, max_methods_per_class, max_file_loc, max_properties_per_class, max_public_methods, max_dependencies, max_efferent_coupling, max_classes_per_file, max_namespace_depth, max_classes_per_namespace, max_traits_per_class, max_interfaces_per_class, max_classes_per_project
- **Complexity (7):** max_cyclomatic_complexity, max_control_nesting, max_weighted_cognitive_density, max_logical_complexity_factor, max_return_statements, max_boolean_conditions, max_try_catch_blocks
- **Breathing (4):** min_code_breathing_score, min_visual_breathing_index, min_identifier_readability_score, min_code_oxygen_level

**Уникальные метрики Soda:**
- Breathing metrics — оценка визуальной «воздушности» кода (блоки, пробелы, длина идентификаторов)
- Weighted Cognitive Density (WCD), Logical Complexity Factor (LCF)

### 2.2 PHPMD — возможности

| Категория | Возможности |
|-----------|-------------|
| **Анализ** | `phpmd <path> <format> <ruleset>` |
| **Правила** | 6 rulesets, ~35 правил |
| **Конфигурация** | XML ruleset, exclude-pattern, properties правил |
| **Отчёты** | text, xml, html, json, checkstyle, junit, sarif |
| **Расширение** | Кастомные правила (PHP-классы), baseline для подавления |

**Rulesets PHPMD:**
- **Unused Code:** UnusedFormalParameter, UnusedPrivateMethod, UnusedLocalVariable, UnusedPrivateField
- **Naming:** BooleanGetMethodName, ConstantNamingConventions, ConstructorWithNameAsEnclosingClass, ShortMethodName, LongVariable, ShortVariable, ShortClassName, LongClassName
- **Design:** CountInLoopExpression, EmptyCatchBlock, DevelopmentCodeFragment, CouplingBetweenObjects, DepthOfInheritance, NumberOfChildren, GotoStatement, EvalExpression, ExitExpression
- **Controversial:** CamelCase* (variable, parameter, method, property, class), Superglobals
- **Code Size:** ExcessiveClassComplexity, TooManyPublicMethods, TooManyMethods, TooManyFields, ExcessivePublicCount, ExcessiveParameterList, ExcessiveClassLength, ExcessiveMethodLength, NPathComplexity, CyclomaticComplexity
- **Clean Code:** ErrorControlOperator, UndefinedVariable, MissingImport, DuplicatedArrayKey, IfStatementAssignment, StaticAccess, ElseExpression, BooleanArgumentFlag

**Уникальные возможности PHPMD:**
- Поиск потенциальных багов (UndefinedVariable, DuplicatedArrayKey, IfStatementAssignment)
- Обнаружение неиспользуемого кода
- Naming conventions (CamelCase, ShortVariable, LongVariable)
- Baseline для постепенного внедрения
- SARIF, Checkstyle — интеграция с GitHub Code Scanning, reviewdog

### 2.3 Сравнение по функционалу

| Функция | Soda | PHPMD |
|---------|------|-------|
| Стиль кода (PSR) | ❌ | Частично (CamelCase) |
| Поиск багов | ❌ | ✅ |
| Неиспользуемый код | ❌ | ✅ |
| Метрики размера | ✅ | ✅ |
| Метрики сложности | ✅ (расширенные) | ✅ (CC, NPath) |
| Breathing/читаемость | ✅ | ❌ |
| Кастомные правила | ❌ | ✅ |
| Baseline | ❌ | ✅ |
| Форматы отчётов | 2 | 7+ |

---

## 3. Анализ кода инструментов

### 3.1 Soda (bunnivo/soda)

| Параметр | Значение |
|----------|----------|
| **Размер** | ~113 PHP-файлов, ~7 150 строк |
| **PHP** | ≥8.3.18 |
| **Зависимости** | nikic/php-parser, sebastian/complexity, sebastian/lines-of-code, Laravel (console, container, support) |
| **Тесты** | PHPUnit 11, unit + end-to-end + verification |
| **CI** | Laravel Pint, PHPStan, Rector, quality, tests |
| **Лицензия** | BSD-3-Clause |

**Качество кода:**
- Строгая типизация (declare(strict_types=1))
- PHPStan в CI
- Современный стек (PHP 8.3+, readonly classes)
- Документация: README, docs/*.md (STRUCTURAL_METRICS, BREATHING_METRICS, COMPLEXITY_READABILITY_METRICS)

### 3.2 PHPMD (phpmd/phpmd)

| Параметр | Значение |
|----------|----------|
| **Размер** | ~670 PHP-файлов, ~42 500 строк |
| **PHP** | ≥5.3.9 |
| **Зависимости** | pdepend/pdepend (основа), composer/xdebug-handler |
| **Тесты** | PHPUnit (через install-test-vendor.php) |
| **CI** | PHP-CS-Fixer, Psalm |
| **Лицензия** | BSD-3-Clause |

**Качество кода:**
- Зрелый проект (с 2009 г.)
- Зависимость от PDepend — отдельный парсер/AST
- Поддержка PHP 5.3+ — legacy-совместимость
- Документация: phpmd.org, rules, documentation

### 3.3 Сравнение по коду

| Критерий | Soda | PHPMD |
|----------|------|-------|
| Поддерживаемость | Выше (меньше кода, современный PHP) | Сложнее (больше кода, legacy) |
| Расширяемость | Ограничена | Высокая (кастомные правила) |
| Активность | Молодой проект | Зрелый, стабильные релизы |
| Сообщество | Небольшое | 2.4k+ stars, активные контрибьюторы |

---

## 4. Производительность

### 4.1 Soda

- **Бенчмарк:** 10k строк < 2 сек (BreathingPerformanceTest)
- **Рекомендация:** 100k строк — memory_limit ≥ 256M
- **Парсер:** nikic/php-parser (быстрый, нативный PHP)

### 4.2 PHPMD

- **Парсер:** PDepend (полный AST, метрики)
- **Особенность:** PDepend тяжёлый — полный граф зависимостей
- **Оценка:** На 50–100k LOC — обычно 30–120 сек (зависит от структуры)

### 4.3 Рекомендация по тестированию

```bash
# Скрипт benchmark.sh
#!/bin/bash
REPO=$1  # путь к репозиторию 50-100k LOC
echo "=== Soda ==="
time php soda quality "$REPO" 2>/dev/null
echo "=== PHPMD ==="
time phpmd "$REPO" text cleancode,codesize,design 2>/dev/null
```

---

## 5. Интеграция

### 5.1 CI/CD

**Soda (GitHub Actions):**

```yaml
- name: Run Soda quality check
  run: php soda quality src
```

**PHPMD (GitHub Actions):**

```yaml
- name: Run PHPMD
  run: phpmd src text cleancode,codesize,design --exclude vendor
```

**Рекомендуемый порядок (Soda README):**
1. Laravel Pint — форматирование
2. Rector — рефакторинг
3. PHPStan/Psalm — статический анализ
4. Soda — quality gates

### 5.2 IDE

| Инструмент | PhpStorm | VSCode |
|------------|----------|--------|
| Soda | Нет плагина | Нет плагина |
| PHPMD | Встроенная поддержка | Расширения (phpmd) |

---

## 6. Отчёты и визуализация

| Формат | Soda | PHPMD |
|--------|------|-------|
| CLI/Text | ✅ | ✅ |
| JSON | ✅ (--report-json) | ✅ |
| HTML | ❌ | ✅ |
| XML | ❌ | ✅ |
| Checkstyle | ❌ | ✅ |
| JUnit | ❌ | ✅ |
| SARIF | ❌ | ✅ |

---

## 7. Рекомендации

### 7.1 Когда выбирать Soda

- Нужны **метрики структуры и читаемости** (длина методов/классов, nesting, breathing)
- Нужен **жёсткий gate по нарушениям** для CI (exit ≠ 0 при любом нарушении)
- Проект на **PHP 8.3+**, современный стек
- Не нужны проверки на баги и неиспользуемый код (есть PHPStan/Psalm)

### 7.2 Когда выбирать PHPMD

- Нужны **поиск багов** (undefined variable, duplicated array key) и **неиспользуемого кода**
- Важны **naming conventions** (CamelCase, short/long names)
- Нужны **кастомные правила** или **baseline**
- Интеграция с **GitHub Code Scanning**, **reviewdog**, **Checkstyle**
- Поддержка **legacy PHP** (5.3+)

### 7.3 Комбинированный подход

**Рекомендация:** Использовать оба инструмента в разных ролях:

1. **PHPMD** — rulesets: cleancode, unusedcode, design (баги, мёртвый код, опасные конструкции)
2. **Soda** — quality gates (структура, сложность, breathing)

```yaml
# CI pipeline
- run: phpmd src text cleancode,unusedcode,design
- run: php soda quality src
```

### 7.4 Ограничения и подводные камни

| Инструмент | Ограничения |
|------------|-------------|
| Soda | Composer install не работает (git clone), нет кастомных правил, нет baseline |
| PHPMD | Медленнее на больших проектах, false positives в naming rules, устаревший PHP 5.3 в require |

---

## 8. Приложения

### A. Пример soda.json

```json
{
  "rules": {
    "structural": {
      "max_method_length": 50,
      "max_class_length": 500,
      "max_arguments": 3
    },
    "complexity": {
      "max_cyclomatic_complexity": 15,
      "max_control_nesting": 3
    },
    "breathing": {
      "min_code_breathing_score": 25
    }
  }
}
```

### B. Пример phpmd.xml

```xml
<?xml version="1.0"?>
<ruleset name="Custom"
         xmlns="http://pmd.sf.net/ruleset/1.0.0"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <rule ref="rulesets/cleancode.xml"/>
  <rule ref="rulesets/codesize.xml"/>
  <rule ref="rulesets/design.xml"/>
  <exclude-pattern>*/vendor/*</exclude-pattern>
</ruleset>
```

### C. GitHub Actions — полный пример

```yaml
name: Quality
on: [push, pull_request]
jobs:
  quality:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
          tools: composer
      - run: composer install --no-interaction
      - name: PHPMD
        run: vendor/bin/phpmd src text cleancode,unusedcode,design --exclude vendor
      - name: Soda
        run: php soda quality src
```

---

*Документ подготовлен на основе анализа исходного кода, документации и публичных источников. Актуальность данных — март 2025.*
