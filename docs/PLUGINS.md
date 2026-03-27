# Soda Plugin System

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
