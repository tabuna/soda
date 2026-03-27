# Аудит архитектуры Soda (качество кода)

Документ фиксирует текущее состояние репозитория (`src/Quality`, команды, тесты) и предлагает целевую модель, расширяемость и roadmap. Ориентир по DX — предсказуемость и короткие классы в духе Laravel (без обязательного полного Clean Architecture в микропакете).

---

## 1. Текущее состояние (кратко)

| Область | Оценка | Комментарий |
|--------|--------|-------------|
| Архитектура | Умеренно слоистая | Есть разделение «сбор метрик → оценка правил → вывод», но границы размыты |
| Расширяемость правил | Средняя | Есть `RuleChecker`, но правило трогает много файлов |
| Читаемость | Хорошая локально | Отдельные checkers читаемы; «склейка» в `QualityAnalyser` перегружена |
| Тестируемость | Хорошая для ядра | `QualityEngine` и visitors хорошо покрыты; CLI и полный пайплайн — слабее |
| Конфигурация правил | Пороги + секции | Нет явного «выключить правило» без изменения кода |
| Производительность | Последовательный обход файлов | Узкое место — парсинг + несколько visitor’ов на файл |

---

## 2. Архитектура (3.1)

### 2.1 Стиль

Фактически **монолитный пакет** с **неформальными слоями**:

1. **CLI** — `Commands\QualityCommand`, `AnalyseCommand` и т.д.
2. **Оркестрация качества** — `QualityAnalyser`: цикл по файлам, парсер, связка visitor’ов, `BreathingAnalyser`, сбор `EvaluateInput`.
3. **Оценка** — `QualityEngine` + `EvaluationContext` + набор `RuleChecker`.
4. **Сбор LOC/структуры** — `ProjectMetrics`, `FileAnalyser`, structure metrics (отдельная ветка от quality).

Нет выделенного **Application / Domain / Infrastructure** как пакетов или модулей — всё в `Bunnivo\Soda\*`, что нормально для CLI-утилиты, но усложняет смену транспорта (HTTP, плагин IDE).

### 2.2 SRP

**Сильные стороны:**

- `MethodChecker` / `ClassChecker` сфокусированы на проверках с fluent `ViolationBuilder`.
- Отдельные visitor’ы (`TryCatchCountVisitor`, `EfferentCouplingVisitor`, …) — узкая ответственность.

**Проблемные зоны:**

- `QualityAnalyser` совмещает: чтение файла, parse, регистрацию ~10 visitor’ов, слияние efferent coupling в метрики, breathing, игнорирование ошибок парсера, резолв конфига и вызов движка. Это **God-orchestrator** (~160 строк в одном методе пайплайна + `analyseFile`).

```27:85:src/Quality/QualityAnalyser.php
final class QualityAnalyser
{
    public function analyse(array $files, bool $debug, ?string $configPath = null): QualityResult
    {
        $metrics = new ProjectMetrics();
        $result = $metrics->analyse($files, $debug);
        // ... накопление массивов по файлам ...
        $config = ConfigResolver::resolveConfig($files, $configPath);
        $engine = QualityEngine::create($config);
        // ...
        return $engine->evaluate($result, $input);
    }
```

- `QualityEngine` держит **дублирующий каталог** штрафов `PENALTIES` рядом с бизнес-логикой score — это второй «реестр правил» рядом с `RuleMetadata` и `*RuleDefaults`.

### 2.3 Coupling / cohesion

- **Связность** внутри `Rule\*` и visitor’ов в целом высокая.
- **Связанность:** правила завязаны на **плоский** `QualityConfig.rules` и строковые ключи; любое новое правило тянет изменения в нескольких константных списках (см. ниже).

### 2.4 Границы модулей

Явных модульных границ (composer packages, bounded contexts) нет. Логические области: `Breathing`, `Structure`, `Quality`, `Commands` — по папкам видно, но **импорты не изолированы** (например, quality тянет breathing напрямую).

---

## 3. Расширяемость (3.2)

### 3.1 Что уже есть (extension points)

- **`RuleChecker`** — единый контракт входа в правила:

```11:17:src/Quality/Rule/RuleChecker.php
interface RuleChecker
{
    /**
     * @return Collection<int, Violation>
     */
    public function check(EvaluationContext $context): Collection;
}
```

- **`RuleRegistry::default()`** — центральная регистрация checkers (ручной список).
- **Visitor** (nikic/php-parser) — основной способ извлечения метрик на уровне AST.
- **Стратегия** — набор взаимозаменяемых `RuleChecker` в `QualityEngine` (простая композиция, не полноценный pipeline с стадиями).

### 3.2 Сколько шагов для «нового правила» сегодня

Типичный чеклист (фактический технический долг):

1. Добавить ключ в `QualityConfig::DEFAULT_RULES` и при необходимости в `soda.json` / документацию.
2. Добавить правило в `RuleSections::sections()` (если относится к секции).
3. Реализовать извлечение метрики (новый visitor или расширение `QualityMetricsVisitor`) и **подключить** в `QualityAnalyser::analyseFile()`.
4. Реализовать проверку в `MethodChecker` / `ClassChecker` / отдельном `RuleChecker`.
5. Дублировать метаданные в `RuleMetadata` через `*RuleDefaults`.
6. Добавить штраф в `QualityEngine::PENALTIES` (иначе fallback `?? 2`).
7. Тест: провайдер в `QualityEngineTest` и/или unit visitor/checker.

**Риск:** рассинхрон между `PENALTIES`, `RuleSections`, `RuleMetadata` и реальными checkers.

### 3.3 Паттерны

| Паттерн | Использование |
|--------|----------------|
| Visitor | Да, повсеместно для AST |
| Strategy | Да, `list<RuleChecker>` |
| Pipeline | Нет как явной абстракции; есть линейный foreach файлов и один проход traverser с цепочкой visitor’ов |

---

## 4. Читаемость и DX (3.3)

**Плюсы:**

- Именование правил в конфиге стабильное (`max_*`, `min_*`).
- Fluent API для нарушений в духе «Laravel airiness» (`whenExceeded` → `file` → `method` → …).
- `readonly` классы, `declare(strict_types=1)`, коллекции Illuminate.

**Минусы для нового разработчика:**

- Точка входа качества размазана: «метрики собираются здесь, пороги там, подписи правил ещё в трёх `*Defaults`».
- `Application` extends `Illuminate\Container`, но **нет биндингов** для домена качества; команды по умолчанию делают `new QualityAnalyser()`:

```58:60:src/Commands/QualityCommand.php
        $analyser = new QualityAnalyser();
        $configPath = $this->resolveConfigPath();
        $result = $analyser->analyse($files, (bool) $this->option('debug'), $configPath);
```

- Строковые ключи правил без enum / value object на границе конфига — быстро, но легко опечататься (PHPStan не ловит ключи JSON).

---

## 5. Тестируемость (3.4)

**Есть:**

- `tests/unit` — широкое покрытие visitors, `QualityEngine`, rule checkers, config.
- `tests/end-to-end` — `.phpt`.
- `tests/verification` — регрессии/перф (отдельный suite).

**Ограничения:**

- Крупные сценарии движка завязаны на **ручную сборку** массивов метрик в провайдерах — это мощно, но многословно; нет фабрики «типичный проект с одним классом».
- Команды не тестируются через интеграцию с подменённым анализатором (нет seam в конструкторе `QualityCommand`).

---

## 6. Правила анализа (3.5)

**Где описаны:**

- Пороги: `QualityConfig`, файл `soda.json` (вложенные секции → flatten в `mergeRules`).
- Секции: `RuleSections`.
- Презентация (severity, label): `RuleMetadata` + `StructureRuleDefaults`, `MethodRuleDefaults`, и т.д.

**Единый интерфейс для правил:** да — `RuleChecker` для фазы оценки; для фазы извлечения метрик интерфейса нет (жёсткий список в `QualityAnalyser`).

**Включение/выключение:** нет первоклассной поддержки в конфиге (например `"enabled": false` или `null` = off). Сейчас отключение = убрать checker из реестра или выставить нереалистичный порог.

**Группировка:** да, в JSON по секциям `structural` / `complexity` / `breathing` / `naming`.

---

## 7. Производительность (3.6)

**Узкие места:**

- Для каждого файла: полный parse + один `NodeTraverser` с множеством visitor’ов — уже лучше, чем N отдельных обходов, но стоимость линейна по числу файлов.
- `BreathingAnalyser::analyse` — дополнительная работа по исходнику/AST после visitor’ов.

**Параллелизм:** не используется; потенциал — multiprocessing по файлам (осторожно с памятью) или очередь воркеров на уровне CLI.

**Quick win без архитектуры:** кэш parse по mtime/size (инфраструктурный слой), если повторные прогоны на CI.

---

## 8. Проблемные зоны, риски, техдолг (4.1)

| Зона | Риск | Техдолг |
|------|------|---------|
| Много реестров правил | Дрейф имён и штрафов | `PENALTIES` + `RuleSections` + `*RuleDefaults` + `DEFAULT_RULES` |
| `QualityAnalyser` | Сложно тестировать и менять порядок стадий | Нет абстракции «экстрактор метрик файла» |
| Нет DI в домене | Сложно подменять парсер/реестр в тестах CLI | `new` в командах |
| Нет disable rules в конфиге | Продуктовая гибкость | Обходные пороги |
| Строковые ключи правил | Ошибки времени выполнения / конфига | Enum или RuleId VO (опционально) |

---

## 9. Целевая архитектура (4.2)

Рекомендуемый стиль для пакета такого размера: **Ports & Adapters (лёгкий hexagonal)** без избыточного количества слоёв.

```
┌─────────────────────────────────────────────────────────┐
│  Delivery (CLI / будущий HTTP)                           │
│  QualityCommand → вызывает Application API               │
└───────────────────────────┬─────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────┐
│  Application                                              │
│  RunQualityAnalysis (один use-case):                      │
│    files → FileDiscoveryPort → foreach FileSource         │
│         → ParsePort → MetricSnapshot                      │
│         → QualityEngine.evaluate                          │
└───────────────────────────┬─────────────────────────────┘
                            │
        ┌───────────────────┴───────────────────┐
        ▼                                       ▼
┌───────────────┐                     ┌──────────────────┐
│  Core Quality │                     │  Metrics Core     │
│  RuleChecker  │                     │  Visitors pipeline│
│  Violation    │                     │  Breathing (opt)  │
│  QualityConfig│                     │  DTO snapshot     │
└───────────────┘                     └──────────────────┘
        ▲                                       ▲
        │                                       │
┌───────┴───────────────────────────────────────┴─────────┐
│  Infrastructure                                          │
│  Sebastian FileIterator, ParserFactory, ConfigLoader     │
└──────────────────────────────────────────────────────────┘
```

**Ответственность слоёв:**

- **Core (анализ/оценка):** `EvaluationContext`, `QualityEngine`, `Violation`, контракт правил.
- **Rules:** только реализации `RuleChecker` + единый **RuleCatalog** (см. ниже).
- **Infrastructure:** файловая система, JSON, PhpParser.
- **API/CLI:** тонкие команды, форматирование (`ReportFormatter`, `JsonResultFormatter`).

---

## 10. Механизм расширяемости (4.4)

### 10.1 Единый RuleCatalog (рекомендация)

Один объект (или статическая фабрика с одним источником правды), из которого генерируется:

- ключ в конфиге;
- секция для JSON;
- label/severity/comparison для отчёта;
- вес для score;
- опционально: `enabled` по умолчанию.

Пример формы API (упрощённо):

```php
final readonly class RuleDefinition
{
    public function __construct(
        public string $id,
        public string $section,
        public string $label,
        public string $severity, // error|warning
        public string $comparison, // max|min
        public int $defaultLimit,
        public int $penaltyPoints,
    ) {}
}

/** @return array<string, RuleDefinition> */
function sodaRules(): array { /* один список */ }
```

Реестр checkers тогда регистрируется либо вручную с ссылкой на `id`, либо через атрибуты/конвенцию (если захотите plugin-style).

### 10.2 Pipeline метрик файла

Вынести из `QualityAnalyser` список visitor’ов в **регистрируемый pipeline**:

```php
interface FileMetricPass
{
    public function beforeTraverse(): void;

    public function visitors(): array; // NodeVisitor

    public function afterTraverse(RedundantNamingVisitor $naming, /* ... */): void; // или собственный контекст
}
```

На практике проще стартовать с **одного класса `QualityAstPipeline`** с методом `visitors(): array` и фабрикой, чтобы не раздувать интерфейсы.

### 10.3 События

`illuminate/events` уже в зависимостях. Опционально: `MetricsExtracted`, `ViolationCreated` — полезно для плагинов и телеметрии, не обязательно для v1.


---

## 11. Roadmap рефакторинга (4.5)

Детальный бэклог с **критериями приёмки по задачам**: [ARCHITECTURE_AUDIT_TASKS.md](./ARCHITECTURE_AUDIT_TASKS.md).

### Quick wins (1–2 недели)

1. **Свести метаданные правила в одну таблицу** (хотя бы `RuleCatalog` + генерация `PENALTIES` и проверка, что каждый `RuleChecker` использует известные id).
2. Выделить **`FileAnalysisService`** (или `AnalysePhpFile`) из `QualityAnalyser`: вход путь/содержимое, выход DTO `FileQualityMetrics`. `QualityAnalyser` только цикл + merge.
3. В `QualityCommand` — конструктор с опциональным `QualityAnalyser` для тестов (или фабрика из контейнера).
4. Документ **«Как добавить правило»** (5–7 шагов) рядом с кодом — один файл в `docs/`.

### Среднесрочно (1–2 месяца)

1. **Конфиг: `enabled` на правило** или `rules.structural.max_method_length: null` = отключено; фильтрация checkers или ранний return в checker.
2. **Типобезопасные идентификаторы** — backed enum `RuleId` в PHP (ключи JSON остаются строками, маппинг в одном месте).
3. Интеграционный тест: `QualityCommand` с временной директорией и минимальным `soda.json`.
4. Объединить дубли списков правил (`RuleSections` vs defaults) — генерировать секции из каталога.

---

## 12. Рекомендации по DX «как в Laravel» (4.7)

**Уточнение:** речь не о превращении Soda в Laravel, а о **качестве кода и предсказуемости для контрибьюторов** (короткие классы, fluent там, где повтор, соглашения об именах). **Отличный UX для пользователя `soda quality`** (ошибки, итог прогона, README, JSON) вынесен в эпик **G** бэклога [ARCHITECTURE_AUDIT_TASKS.md](./ARCHITECTURE_AUDIT_TASKS.md) — без него архитектурные шаги не гарантируют «восторг пользователя».

1. **Один очевидный entry point для разработчика правила:** `php artisan make:rule` аналог не нужен, но **один класс-шаблон** и раздел в docs достаточны.
2. **Короткие классы:** держать checkers < ~150 строк; составные правила разбивать на private методы или под-классы проверок.
3. **Fluent там, где повторяется** (уже есть `ViolationBuilder`) — не дублировать массивы нарушений вручную.
4. **Контейнер:** минимальный `AppServiceProvider`-подобный класс `SodaApplication` с `bind` парсера и реестра правил — без полного Laravel.
5. **Предсказуемые имена:** `*Checker` для правил, `*Visitor` для AST; метрики проекта — `ProjectMetrics` / `FileAnalyser`; качество — `QualityAnalyser` / фасад `Analyzer`.

Пример целевого «тонкого» use-case (иллюстрация):

```php
final readonly class AnalyseQuality
{
    public function __construct(
        private ConfigLoader $config,
        private FileAnalyser $files,
        private PhpFileMetricsExtractor $extract,
        private QualityEngine $engine,
    ) {}

    /** @param list<string> $paths */
    public function run(array $paths, ?string $configPath): QualityResult
    {
        $files = $this->files->list($paths);
        $cfg = $this->config->resolve($files, $configPath);
        $snapshot = $this->extract->collect($files);
        return $this->engine->evaluate($snapshot->projectResult(), $snapshot->evaluateInput($cfg));
    }
}
```

---

## 13. Критерии успеха (из ТЗ)

| Критерий | Как измерить после изменений |
|----------|------------------------------|
| Новый разработчик понимает архитектуру | Онбординг-страница + схема из §9; время до первого PR с правилом |
| Добавление правила — минимум времени | Чеклист сокращён за счёт `RuleCatalog` + одного места регистрации visitor’ов |
| Предсказуемость кода | Меньше ручных списков; статический анализ на полноту каталога |
| Снижение связности | Меньше импортов из `QualityAnalyser` в новые фичи |
| Покрытие тестами | Покрытие CLI/use-case; не падать только на engine |
| Пользователь CLI доволен | Критерии эпика **G** в [ARCHITECTURE_AUDIT_TASKS.md](./ARCHITECTURE_AUDIT_TASKS.md) (итог PASS/FAIL, ошибки с путём, README/CI) |

---

## 14. Резюме

Проект уже на **здоровой базе**: интерфейс `RuleChecker`, visitor’ы, сильные unit-тесты движка, секционный конфиг. Главный техдолг — **фрагментация описания правил** и **перегруженный оркестратор** `QualityAnalyser`. Целевой шаг — **единый каталог правил + выделение извлечения метрик файла**, затем **флаги включения** и лёгкий DI на границе CLI. Это даёт Laravel-подобную предсказуемость без превращения пакета в тяжёлую Clean Architecture.
