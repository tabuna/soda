# Как добавить правило качества

> **Рекомендуемый путь** — используйте `SodaRule` и подключайте через `soda.php`.  
> Никаких изменений в ядре не требуется.

## Быстрый старт (для потребителей пакета)

Создайте класс, унаследованный от `SodaRule`, и подключите его в `soda.php`:

```php
<?php // app/Quality/NoVarDumpRule.php

use Bunnivo\Soda\Config\SodaRule;

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

```php
<?php // soda.php

use Bunnivo\Soda\Config\SodaConfigurator;

return SodaConfigurator::configure(function ($config) {
    $config->rule(NoVarDumpRule::class);
});
```

Подробнее — [docs/PLUGINS.md](PLUGINS.md).

---

## Добавление встроенного правила (для авторов пакета)

Если вы добавляете правило **в сам пакет** (в форке или как контрибьютор):

1. **Checker** — создайте `final class MyChecker implements RuleChecker` в `src/Quality/Rule/`.
2. **Плагин** — добавьте `new MyChecker` в подходящий плагин (`src/Plugins/`): `StructuralPlugin`, `ComplexityPlugin`, `BreathingPlugin` или `NamingPlugin`.
3. **Тесты** — unit-тест и/или провайдер в `QualityEngineTest`.

`StandardPlugin` агрегирует все 4 плагина → `RuleRegistry::default()` → ядро подхватит автоматически.

Ориентир: checker до ~150 строк; иначе разбейте на private-методы.
