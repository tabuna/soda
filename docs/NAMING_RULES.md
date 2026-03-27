# Naming Rules

Правила именования для устранения избыточности в названиях классов и методов.

**Config:** Rules live under `rules.naming` in `soda.json`.

```json
{
  "rules": {
    "naming": {
      "avoid_redundant_naming": 80
    }
  }
}
```

| Параметр | Описание | По умолчанию |
|----------|----------|--------------|
| `avoid_redundant_naming` | Порог схожести (0 = отключено, 1–100 = %). Минимальная длина слова: 4 | 80 |

---

## avoid_redundant_naming

Находит и предлагает упрощение имён, когда часть имени повторяет информацию из типов параметров, возвращаемого значения или имени класса.

### Алгоритм

1. **Разбивка camelCase** — через `Str::ucsplit()` (Laravel):
   - `PostItemCollection` → `["Post", "Item", "Collection"]`
   - `addPostItem` → `["add", "Post", "Item"]`
   - `getAllUsers` → `["get", "All", "Users"]`

2. **Схожесть слов** — `similar_text()` ≥ 80% (порог настраивается).

3. **Классы** — если имя заканчивается на `Collection`, `Repository`, `Service` и т.п., и перед суффиксом стоит `Item`, `Items`, `Entity`, `Entities`, `Model`, `Models` — предлагается убрать избыточное слово.

4. **Методы add/has/remove** — если имя метода после префикса совпадает с типом первого параметра — предлагается оставить только префикс.

5. **Методы add/has/remove + контекст класса** — `addUser()` в `UserService` → `add()` (сущность из имени класса: UserService → User).

6. **Методы get/find/list** — если метод `getAllX`, `getX` и т.п. возвращает коллекцию — предлагается `all()` или `getAll()`.

### Игнорируется

- Magic-методы (`__construct`, `__toString`, …)
- Классы на `Exception`, `Interface`, `Trait`, `Abstract`, `Test`
- Параметры с типом `*Interface` (например, `addLogger(LoggerInterface $logger)`)

### Примеры

```
Redundant naming detected
  Current: addPostItem(PostItem $...)
  Suggested: add(PostItem $...)
  Reason: "PostItem" already conveyed by parameter type
  Similarity score: 92%
```

```
PostItemCollection → PostCollection
addUserProfileData(UserProfileData $d) → add(UserProfileData $d)
addUser() в UserService → add()
getAllOrders(): Order[] → all() или getAll()
addLogger(LoggerInterface $logger) — не трогать (false-positive)
```
