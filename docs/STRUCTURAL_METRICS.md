# Structural / Formal Metrics

Metrics that measure **size**, **structure**, and **coupling** of code. They are easy to count and enforce.

**Config:** Rules live under `rules.structural` in `soda.json`. Defaults: `QualityConfig::DEFAULT_RULES`. Run `php soda init` for a full config.

```json
{
  "rules": {
    "structural": {
      "max_method_length": 50,
      "max_class_length": 500,
      "max_file_loc": 400,
      "max_arguments": 3
    }
  }
}
```

---

## Size of Classes and Methods

### max_method_length

Maximum lines of code per method.

| Config value | Strictness | Typical use |
|--------------|------------|-------------|
| 20–30 | Strict | New projects |
| 50–80 | Moderate | Legacy refactoring |
| 100+ | Lenient | Temporary |

```php
// ❌ Bad (80+ lines)
public function processOrder(Order $order): void
{
    $order->validate();
    $items = $order->items();
    foreach ($items as $item) {
        $product = $item->product();
        $stock = $product->stock();
        if ($stock < $item->quantity()) {
            throw new InsufficientStockException();
        }
        $stock -= $item->quantity();
        $product->updateStock($stock);
        $this->inventory->log($product, -$item->quantity());
    }
    $order->markAsProcessed();
    $this->notifyWarehouse($order);
    $this->sendConfirmation($order);
    $this->updateAnalytics($order);
    // ... 60 more lines
}
```

```php
// ✅ Good
public function processOrder(Order $order): void
{
    $this->reserveStock($order);
    $order->markAsProcessed();
    $this->notifyWarehouse($order);
    $this->sendConfirmation($order);
}

private function reserveStock(Order $order): void
{
    foreach ($order->items() as $item) {
        $this->inventory->reserve($item->product(), $item->quantity());
    }
}
```

---

### max_class_length

Maximum lines per class. God classes violate Single Responsibility.

| Config value | Strictness |
|--------------|------------|
| 200–350 | Strict |
| 400–500 | Moderate |
| 800+ | Lenient |

```php
// ❌ Bad (500+ lines)
class UserService
{
    // 50 methods: create, update, delete, sendEmail, resetPassword,
    // validateAddress, exportPdf, importCsv, syncWithLdap, ...
}
```

```php
// ✅ Good
class UserService
{
    public function __construct(
        private UserRepository $users,
        private UserMailer $mailer,
    ) {}

    public function create(array $data): User { /* ~15 lines */ }
    public function update(User $user, array $data): void { /* ~10 lines */ }
}
// Separate classes: UserMailer, UserExporter, UserImporter
```

---

### max_file_loc

Maximum lines per file.

| Config value | Strictness |
|--------------|------------|
| 300–400 | Strict |
| 500–600 | Moderate |
| 1000+ | Lenient |

---

## Number of Dependencies

### max_arguments

Maximum parameters per method.

| Config value | Strictness |
|--------------|------------|
| 2–3 | Strict |
| 4–5 | Moderate |
| 6+ | Lenient |

```php
// ❌ Bad (7 args)
public function createUser(
    string $name,
    string $email,
    string $password,
    ?string $phone,
    ?string $address,
    ?string $city,
    ?string $country,
): User
```

```php
// ✅ Good
public function createUser(CreateUserRequest $request): User
{
    return $this->users->create($request->toUserData());
}
```

---

### max_dependencies

Maximum constructor parameters (injected dependencies).

| Config value | Strictness |
|--------------|------------|
| 3–5 | Strict |
| 6–8 | Moderate |
| 10+ | Lenient |

```php
// ❌ Bad (10 dependencies)
public function __construct(
    private UserRepository $users,
    private OrderRepository $orders,
    private ProductRepository $products,
    private Mailer $mailer,
    private Logger $logger,
    private Cache $cache,
    private EventDispatcher $events,
    private Config $config,
    private Validator $validator,
    private PdfGenerator $pdf,
) {}
```

```php
// ✅ Good
public function __construct(
    private OrderRepository $orders,
    private OrderNotifier $notifier,  // encapsulates Mailer, Logger, Events
) {}
```

---

### max_properties_per_class

Maximum properties (fields) per class.

| Config value | Strictness |
|--------------|------------|
| 5–6 | Strict |
| 8–10 | Moderate |
| 15+ | Lenient |

```php
// ❌ Bad (15+ properties)
class Order
{
    public string $id;
    public string $customerName;
    public string $customerEmail;
    public string $customerPhone;
    public string $shippingAddress;
    public string $billingAddress;
    public string $status;
    public float $subtotal;
    public float $tax;
    public float $shipping;
    public float $discount;
    public \DateTime $createdAt;
    public \DateTime $updatedAt;
    public ?\DateTime $shippedAt;
    public ?string $trackingNumber;
}
```

```php
// ✅ Good
class Order
{
    public function __construct(
        private OrderId $id,
        private Customer $customer,
        private Address $shipping,
        private OrderTotals $totals,
        private OrderStatus $status,
    ) {}
}
```

---

### max_public_methods

Maximum public methods per class (API surface).

| Config value | Strictness |
|--------------|------------|
| 8–12 | Strict |
| 15–20 | Moderate |
| 30+ | Lenient |

---

## Structure and Coupling

### max_methods_per_class

Maximum total methods per class.

| Config value | Strictness |
|--------------|------------|
| 15–25 | Strict |
| 30–40 | Moderate |
| 50+ | Lenient |

```php
// ❌ Bad (30+ methods)
class ReportGenerator
{
    public function generatePdf() {}
    public function generateExcel() {}
    public function generateCsv() {}
    public function generateJson() {}
    public function exportToEmail() {}
    public function exportToFtp() {}
    public function scheduleDaily() {}
    // ... 23 more methods
}
```

```php
// ✅ Good
class ReportGenerator
{
    public function __construct(private array $formatters) {}

    public function generate(Report $report, string $format): string
    {
        return $this->formatters[$format]->format($report);
    }
}
```

---

### max_classes_per_file

Maximum classes/interfaces/traits per file.

| Config value | Typical |
|--------------|---------|
| 1 | One class per file (recommended) |
| 2 | Class + its interface |

```php
// ❌ Bad — 4 classes in one file
// UserService.php
class UserService {}
class UserServiceException extends \Exception {}
class UserCreatedEvent {}
class UserValidator {}
```

```php
// ✅ Good — one class per file
// UserService.php
class UserService {}

// UserServiceException.php
class UserServiceException extends \Exception {}
```

---

### max_namespace_depth

Maximum namespace nesting. `App\Domain\User\Repository\Cache\Redis` = 5 levels.

| Config value | Strictness |
|--------------|------------|
| 3–4 | Strict |
| 5 | Lenient |

```php
// ❌ Bad (6+ levels)
namespace App\Domain\Order\Service\Processor\Handler\Validator;
```

```php
// ✅ Good
namespace App\Domain\Order;
// or
namespace App\Order\Processor;
```

---

### max_classes_per_namespace

Maximum classes in one namespace.

| Config value | Typical |
|--------------|---------|
| 16–40 | Recommended |
| 50+ | Consider splitting |

---

### max_traits_per_class

Maximum traits used by a class.

| Config value | Strictness |
|--------------|------------|
| 1–2 | Strict |
| 3 | Lenient |

```php
// ❌ Bad — too many traits
class User extends Model
{
    use HasUuid, HasTimestamps, SoftDeletes, HasEvents, LogsActivity,
        BelongsToTenant, HasPermissions, Cacheable, Searchable;
}
```

```php
// ✅ Good
class User extends Model
{
    use HasUuid, SoftDeletes;
}
```

---

### max_interfaces_per_class

Maximum interfaces implemented by a class.

| Config value | Strictness |
|--------------|------------|
| 1–2 | Strict |
| 3 | Lenient |

```php
// ❌ Bad — too many interfaces
class UserService implements
    UserRepositoryInterface,
    CacheableInterface,
    EventDispatcherInterface,
    LoggableInterface,
    SerializableInterface
{}
```

```php
// ✅ Good
class UserService implements UserRepositoryInterface {}
```

---

### max_classes_per_project

Maximum classes/traits in the project. Indicator of project size.

| Config value | Project size |
|--------------|--------------|
| 500–1000 | Medium |
| 2000–5000 | Large |

---

## Method-level Structure

### max_return_statements

Maximum `return` statements per method.

| Config value | Strictness |
|--------------|------------|
| 1–2 | Strict (single exit) |
| 3–4 | Moderate |
| 5+ | Lenient |

### max_boolean_conditions

Maximum boolean operands (`&&`, `||`) per condition.

| Config value | Strictness |
|--------------|------------|
| 2–3 | Strict |
| 4–5 | Lenient |

```php
// ❌ Bad — 4 boolean operands
if ($user->vip && $order->total > 1000 && $order->valid && $user->hasCoupon()) {
    return 'gold';
}
```

```php
// ✅ Good
if ($this->eligibility->isGoldTier($user, $order)) {
    return 'gold';
}
```
