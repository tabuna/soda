# Soda Plugin System

Soda построен вокруг **системы плагинов**: ядро — это только движок анализа, все правила (включая встроенные) подключаются отдельно.

---

## Архитектура

```
Ядро (анализ AST, метрики, CLI)
    ↓
StandardPlugin          ← все встроенные правила
    ├── StructuralPlugin    (LOC, классы, запахи кода)
    ├── ComplexityPlugin    (сложность, вложенность)
    ├── BreathingPlugin     (метрики дыхания кода)
    └── NamingPlugin        (именование)
    ↓
Пользовательские плагины и правила  ← ваши
```

Каждый блок — это `SodaPlugin`. Вы можете заменить любой блок, добавить свои или отключить все встроенные.

---

## Быстрый старт — своё правило за 2 шага

### Шаг 1. Создайте правило

```php
<?php

use Bunnivo\Soda\Config\SodaRule;

final class MaxFileLoc extends SodaRule
{
    public function id(): string { return 'max_file_loc_custom'; }

    protected function evaluate(string $file, array $metrics): array
    {
        return $this->exceeds($file, $metrics['file_loc'], 300);
    }
}
```

### Шаг 2. Подключите в `soda.php`

```php
$config->rule(MaxFileLoc::class);
```

---

## Сценарии использования

### Добавить правила к стандартным (по умолчанию)

```php
protected function configure(SodaConfig $config): void
{
    $config->structural()->maxMethodLength(100);  // настройка встроенных
    $config->rule(MaxFileLoc::class);             // добавить своё
}
```

### Только нужные группы встроенных правил

```php
use Bunnivo\Soda\Plugins\StructuralPlugin;
use Bunnivo\Soda\Plugins\ComplexityPlugin;

protected function configure(SodaConfig $config): void
{
    $config->withoutBuiltins()               // отключить все встроенные
           ->plugin(StructuralPlugin::class) // включить только структуру
           ->plugin(ComplexityPlugin::class) // и сложность
           ->rule(MyRule::class);            // + своё правило
}
```

### Полностью своя система правил

```php
protected function configure(SodaConfig $config): void
{
    $config->withoutBuiltins()     // ничего встроенного
           ->plugin(MyPlugin::class);
}
```

### Все встроенные явно (максимальная прозрачность)

```php
use Bunnivo\Soda\Plugins\StandardPlugin;

protected function configure(SodaConfig $config): void
{
    $config->withoutBuiltins()
           ->plugin(StandardPlugin::class)  // = все 4 группы
           ->rule(MyRule::class);
}
```

---

## Встроенные плагины

| Класс               | Что проверяет                                                     |
|---------------------|-------------------------------------------------------------------|
| `StructuralPlugin`  | LOC, классы, методы, зависимости, запахи кода, неймспейсы        |
| `ComplexityPlugin`  | Цикломатическая сложность, вложенность, когнитивная плотность     |
| `BreathingPlugin`   | CBS, VBI, IRS, COL — метрики читаемости кода                     |
| `NamingPlugin`      | Избыточные названия, префиксы булевых методов                    |
| `StandardPlugin`    | Все четыре группы вместе (удобный ярлык)                         |

---

## Создание собственного правила

### Вариант 1: `SodaRule` — минимально

```php
final class MaxClassMethods extends SodaRule
{
    public function id(): string { return 'max_class_methods'; }

    protected function evaluate(string $file, array $metrics): array
    {
        $violations = [];

        foreach ($metrics['classes'] as $class => $data) {
            array_push($violations,
                ...$this->exceeds($file, $data['methods'], 15, class: $class)
            );
        }

        return $violations;
    }
}

// Регистрация:
$config->rule(MaxClassMethods::class);
```

### Вариант 2: `RuleChecker` — полный контроль

```php
use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\Rule\RuleChecker;
use Illuminate\Support\Collection;

final class MyAdvancedRule implements RuleChecker
{
    public function check(EvaluationContext $context): Collection
    {
        // $context->config          — конфигурация (пороги, правила)
        // $context->projectMetrics  — метрики всего проекта
        // $context->fileMetrics     — метрики по файлам
        return collect([]);
    }
}
```

### Вариант 3: `SodaPlugin` — пакет правил

```php
final class MyCompanyPlugin implements SodaPlugin
{
    public function checkers(): array
    {
        return [
            new MaxClassMethods,
            new NoDebugCalls,
            new RequireDocBlocks,
        ];
    }
}

// Регистрация:
$config->plugin(MyCompanyPlugin::class);
```

---

## Хелперы `SodaRule`

```php
// Нарушение, если value > limit
$this->exceeds($file, $value, $limit, class: 'Foo', method: 'bar', line: 42);

// Нарушение, если value < limit
$this->below($file, $value, $limit);
```

Оба возвращают `list<Violation>` — пустой массив при отсутствии нарушения.

### Доступные ключи `$metrics`

| Ключ            | Тип     | Описание                                                              |
|-----------------|---------|-----------------------------------------------------------------------|
| `file_loc`      | `int`   | LOC файла                                                             |
| `classes_count` | `int`   | Количество классов                                                    |
| `classes`       | `array` | По классам: `loc`, `methods`, `properties`, `dependencies`, `efferent_coupling`, `traits`, `interfaces`, `namespace`, `namespace_depth` |
| `methods`       | `array` | По методам: `loc`, `args`                                             |
| `namespaces`    | `array` | Неймспейс → количество классов                                       |
| `breathing`     | `array` | Метрики дыхания: CBS, VBI, IRS, COL, LCF, WCD                       |

---

## Полный пример `soda.php`

```php
<?php

use Bunnivo\Soda\Config\SodaConfig;
use Bunnivo\Soda\Config\SodaConfigurator;
use Bunnivo\Soda\Plugins\StructuralPlugin;
use Bunnivo\Soda\Plugins\ComplexityPlugin;

class SodaRules extends SodaConfigurator
{
    protected function configure(SodaConfig $config): void
    {
        // Выбрать группы встроенных правил:
        $config->withoutBuiltins()
               ->plugin(StructuralPlugin::class)
               ->plugin(ComplexityPlugin::class);

        // Настроить пороги:
        $config->structural()
               ->maxMethodLength(100)
               ->maxClassLength(500)
               ->maxArguments(3);

        $config->complexity()
               ->maxCyclomaticComplexity(10)
               ->maxControlNesting(3);

        // Добавить свои правила:
        $config->rule(MaxFileLoc::class)
               ->plugin(CompanyPlugin::class);
    }
}

return SodaConfigurator::entry(SodaRules::class);
```

---

## Свои метрики в `analyze()`

`SodaRule` даёт полную свободу в сборе данных — независимо от встроенного pipeline.

### Как это работает

```
check(EvaluationContext)
    ↓
для каждого файла:
    builtinMetrics = встроенные метрики (file_loc, classes, …)
    customMetrics  = $this->analyze($file)    ← ваш код
    metrics        = merge(builtinMetrics, customMetrics)
    violations     = $this->evaluate($file, metrics)
```

Ключи из `analyze()` доступны в `evaluate()` наравне со встроенными. Вы можете даже переопределить встроенные ключи.

### Пример: regex-проверка

```php
final class NoVarDumpRule extends SodaRule
{
    public function id(): string { return 'no_var_dump'; }

    protected function analyze(string $file): array
    {
        preg_match_all('/var_dump\s*\(/', $this->contents($file), $m);
        return ['var_dump_count' => count($m[0])];
    }

    protected function evaluate(string $file, array $metrics): array
    {
        return $this->exceeds($file, $metrics['var_dump_count'], 0);
    }
}
```

### Пример: AST-проверка через PHP-Parser

```php
use PhpParser\Node\Stmt\Global_;

final class NoGlobalKeywordRule extends SodaRule
{
    public function id(): string { return 'no_global_keyword'; }

    protected function analyze(string $file): array
    {
        $count = count(array_filter(
            $this->parse($file),
            fn($node) => $node instanceof Global_
        ));
        return ['global_count' => $count];
    }

    protected function evaluate(string $file, array $metrics): array
    {
        return $this->exceeds($file, $metrics['global_count'], 0);
    }
}
```

### Пример: полностью своя проверка без встроенных метрик

```php
final class FileSizeRule extends SodaRule
{
    public function id(): string { return 'max_file_size_kb'; }

    protected function analyze(string $file): array
    {
        return ['file_size_kb' => (int) round(strlen($this->contents($file)) / 1024)];
    }

    protected function evaluate(string $file, array $metrics): array
    {
        return $this->exceeds($file, $metrics['file_size_kb'], 50);
    }
}
```

### Хелперы для `analyze()`

| Метод                        | Что делает                                      |
|------------------------------|-------------------------------------------------|
| `$this->contents($file)`     | Читает исходный код файла как строку            |
| `$this->parse($file)`        | Парсит PHP файл в AST (`list<\PhpParser\Node>`) |
