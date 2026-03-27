# JSON-отчёт `soda quality --report-json=`

Версия схемы: поле верхнего уровня `schema_version` (целое). Текущее значение: **2** (поле `score` удалено).

Структура:

| Поле | Тип | Описание |
|------|-----|----------|
| `schema_version` | int | Версия формата (breaking changes → увеличить и зафиксировать в CHANGELOG) |
| `metrics` | object | Тот же формат, что у `JsonResultFormatter` для обычного `analyse` |
| `violations` | array | Список объектов нарушений (`Violation::toArray()`) |

При изменении структуры в мажорной версии пакета обновляйте `schema_version` и этот файл.
