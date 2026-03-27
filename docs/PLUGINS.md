# Soda Plugin System

Soda поддерживает **систему плагинов**, позволяющую подключать пользовательские правила качества без изменения ядра.

---

## Быстрый старт — одно правило за 2 шага

### Шаг 1. Создайте правило

Расширьте `SodaRule` и реализуйте два метода:

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
protected function configure(SodaConfig $config): void
{
    $config->rule(MaxFileLoc::class);
}
```

Всё. Запустите проверку:

```bash
php soda quality src/
```

---

## `SodaRule` — базовый класс

```php
abstract class SodaRule implements RuleChecker
{
    abstract public function id(): string;               // уникальный ID правила
    abstract protected function evaluate(string $file, array $metrics): array; // ваша логика

    // Хелперы — возвращают violation или []
    protected function exceeds(string $file, int|float $value, int|float $limit,
        ?string $class = null, ?string $method = null, ?int $line = null): array;

    protected function below(string $file, int|float $value, int|float $limit,
        ?string $class = null, ?string $method = null, ?int $line = null): array;
}
```

`SodaRule` автоматически итерирует все файлы и собирает нарушения. Вы пишете только логику для одного файла.

### Доступные ключи в `$metrics`

| Ключ              | Тип            | Описание                                                     |
|-------------------|----------------|--------------------------------------------------------------|
| `file_loc`        | `int`          | LOC файла                                                    |
| `classes_count`   | `int`          | Количество классов                                           |
| `classes`         | `array`        | Данные по классам: `loc`, `methods`, `properties`, `dependencies`, `efferent_coupling`, `traits`, `interfaces`, `namespace`, `namespace_depth` |
| `methods`         | `array`        | Данные по методам: `loc`, `args`                            |
| `namespaces`      | `array`        | Неймспейс → количество классов                              |
| `breathing`       | `array`        | Метрики дыхания: CBS, VBI, IRS, COL, LCF, WCD              |

### Проверка на уровне классов

```php
protected function evaluate(string $file, array $metrics): array
{
    $violations = [];

    foreach ($metrics['classes'] as $class => $data) {
        array_push($violations, ...$this->exceeds($file, $data['methods'], 20, class: $class));
    }

    return $violations;
}
```

### Проверка на уровне методов

```php
protected function evaluate(string $file, array $metrics): array
{
    $violations = [];

    foreach ($metrics['methods'] as $method => $data) {
        array_push($violations, ...$this->exceeds($file, $data['loc'], 50, method: $method));
    }

    return $violations;
}
```

---

## Несколько правил в одном файле — `SodaPlugin`

Когда правил несколько, объедините их в плагин:

```php
<?php

use Bunnivo\Soda\Config\SodaPlugin;

final class MyPlugin implements SodaPlugin
{
    public function checkers(): array
    {
        return [
            new MaxFileLoc,
            new MaxMethodCount,
            new MinBreathingScore,
        ];
    }
}
```

Регистрация:

```php
$config->plugin(MyPlugin::class);
```

`plugin()` и `rule()` можно комбинировать:

```php
$config->plugin(MyPlugin::class)
       ->rule(AnotherRule::class);
```

---

## Полный пример `soda.php` с правилами

```php
<?php

use Bunnivo\Soda\Config\SodaConfig;
use Bunnivo\Soda\Config\SodaConfigurator;

class SodaRules extends SodaConfigurator
{
    protected function configure(SodaConfig $config): void
    {
        // стандартные правила
        $config->structural()->maxMethodLength(100)->maxClassLength(500);
        $config->complexity()->maxCyclomaticComplexity(10);

        // пользовательские правила
        $config->rule(MaxFileLoc::class);
        $config->plugin(CompanyQualityPlugin::class);
    }
}

return SodaConfigurator::entry(SodaRules::class);
```

---

## Правила создания

| Требование                                 | Описание                                                       |
|--------------------------------------------|----------------------------------------------------------------|
| Расширяет `SodaRule` (или `RuleChecker`)  | `SodaRule` проще; `RuleChecker` — для полного контроля        |
| `id()` возвращает уникальный snake_case    | Используется в отчёте нарушений                               |
| `evaluate()` возвращает `list<Violation>`  | Используйте `exceeds()` / `below()` или `ViolationBuilder`   |
| Класс должен существовать                  | Иначе `InvalidArgumentException` при старте                   |
| Не зависит от других правил                | Каждое правило изолировано                                     |


Soda поддерживает **систему плагинов**, позволяющую подключать пользовательские правила качества без изменения ядра.

---

## Быстрый старт

### 1. Создайте плагин

Реализуйте интерфейс `SodaPlugin` и верните список экземпляров `RuleChecker`:

```php
<?php

use Bunnivo\Soda\Config\SodaPlugin;

final class MyPlugin implements SodaPlugin
{
    public function checkers(): array
    {
        return [
            new MaxDocBlockLengthRule(),
            new ForbiddenFunctionsRule(),
        ];
    }
}
```

### 2. Создайте правило

Реализуйте интерфейс `RuleChecker`. Метод `check()` получает `EvaluationContext` с метриками файла и конфигурацией, а возвращает коллекцию `Violation`:

```php
<?php

use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\Rule\RuleChecker;
use Bunnivo\Soda\Quality\RuleChecker as RuleCheckerFluent;
use Illuminate\Support\Collection;

final class MaxDocBlockLengthRule implements RuleChecker
{
    private const string RULE_ID = 'max_docblock_length';
    private const int    LIMIT   = 20;

    public function check(EvaluationContext $context): Collection
    {
        $violations = collect();

        foreach ($context->fileMetrics->methods() as $method) {
            $violations->push(
                ...RuleCheckerFluent::whenExceeded(self::RULE_ID)
                    ->file($context->fileMetrics->filePath())
                    ->class($method->className())
                    ->method($method->name())
                    ->forValue($method->docBlockLines())
                    ->limit(self::LIMIT)
                    ->result()
            );
        }

        return $violations;
    }
}
```

### 3. Подключите плагин в `soda.php`

```php
<?php

use Bunnivo\Soda\Config\SodaConfig;
use Bunnivo\Soda\Config\SodaConfigurator;

class SodaRules extends SodaConfigurator
{
    protected function configure(SodaConfig $config): void
    {
        // стандартные правила...
        $config->structural()->maxMethodLength(100);

        // подключение плагина
        $config->plugin(MyPlugin::class);
    }
}

return SodaConfigurator::entry(SodaRules::class);
```

---

## Интерфейсы

### `SodaPlugin`

```php
interface SodaPlugin
{
    /** @return list<RuleChecker> */
    public function checkers(): array;
}
```

Один плагин может возвращать несколько правил. Правила выполняются независимо — ошибка в одном не влияет на остальные.

### `RuleChecker`

```php
interface RuleChecker
{
    /** @return Collection<int, Violation> */
    public function check(EvaluationContext $context): Collection;
}
```

### `EvaluationContext`

| Поле             | Тип                   | Описание                                         |
|------------------|-----------------------|--------------------------------------------------|
| `$config`        | `QualityConfig`       | Пороги и настройки из `soda.php`                |
| `$projectMetrics`| `Result`              | Агрегированные метрики проекта (LOC, классы…)   |
| `$fileMetrics`   | `FileMetrics`         | Метрики текущего анализируемого файла           |

---

## Регистрация нескольких плагинов

```php
$config->plugin(SecurityPlugin::class);
$config->plugin(ArchitecturePlugin::class);
$config->plugin(NamingPlugin::class);
```

Метод `plugin()` — fluent, возвращает `$this`. Плагины подключаются в порядке регистрации и работают независимо друг от друга.

---

## Правила создания плагинов

| Требование                          | Описание                                                      |
|-------------------------------------|---------------------------------------------------------------|
| Реализует `SodaPlugin`             | Обязательно                                                   |
| Возвращает `list<RuleChecker>`     | `checkers()` должен возвращать массив реализаций `RuleChecker`|
| Не изменяет ядро                   | Плагин не должен модифицировать файлы в `src/`               |
| Независимость                      | Плагины не должны зависеть друг от друга                      |
| Класс-плагин должен существовать   | Иначе будет выброшен `InvalidArgumentException`              |

---

## Пример: минималистичный плагин

```php
<?php

use Bunnivo\Soda\Config\SodaPlugin;
use Bunnivo\Soda\Quality\EvaluationContext;
use Bunnivo\Soda\Quality\Rule\RuleChecker;
use Illuminate\Support\Collection;

final class NoDebugCallsRule implements RuleChecker
{
    public function check(EvaluationContext $context): Collection
    {
        // ваша логика анализа...
        return collect();
    }
}

final class MyCompanyPlugin implements SodaPlugin
{
    public function checkers(): array
    {
        return [new NoDebugCallsRule()];
    }
}
```

Зарегистрируйте в `soda.php`:

```php
$config->plugin(MyCompanyPlugin::class);
```

Запустите проверку:

```bash
php soda quality src/
```
