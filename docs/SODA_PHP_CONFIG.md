# Конфиг порогов: `soda.php`

Пороги задаются только через **`soda.php`** / **`.soda.php`**. Рекомендуемый стиль — **класс**, наследующий {@see `Bunnivo\Soda\Config\SodaConfigurator`}: внутри `configure(SodaConfig $config)` IDE даёт автодополнение по fluent-методам (`$config->structural()->maxMethodLength(...)` и т.д.).

## Шаблон

```php
<?php

declare(strict_types=1);

use Bunnivo\Soda\Config\SodaConfig;
use Bunnivo\Soda\Config\SodaConfigurator;

class SodaRules extends SodaConfigurator
{
    protected function configure(SodaConfig $config): void
    {
        $config->structural()->maxMethodLength(100)->maxClassLength(500);
        $config->complexity()->maxCyclomaticComplexity(10);
        $config->breathing()->minCodeBreathingScore(70);
    }
}

return SodaConfigurator::entry(SodaRules::class);
```

Расширение: класс из `init` объявлен без `final` — можно наследовать `SodaRules` и вызывать `parent::configure($config)` при необходимости.

Либо возвращайте любой `callable(SodaConfig): void` без базового класса — тогда автодополнение только внутри замыкания на параметре `SodaConfig $config`.

## `soda init`

Создаёт `soda.php` с классом `SodaRules` и полным набором вызовов fluent (через внутренний генератор исходного кода).

## `config/soda.php`

Отдельный файл для **дополнительных** `RuleChecker` (массив классов), не для порогов.

## Примеры

- [examples/soda.config.php](../examples/soda.config.php)
- [soda.php](../soda.php) в корне репозитория
