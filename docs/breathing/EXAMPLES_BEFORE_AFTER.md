# Breathing Metrics: Before/After Examples

Real-world refactoring patterns that improve CBS, VBI, COL, and IRS.

---

## Example 1: Dense loop → Fluent chain

**Before (CBS ~0.15, COL &lt; 0.1)**

```php
public function getActiveTotal(array $items): float
{
    $t=0;foreach($items as $i){if($i['active']){$t+=$i['price']*$i['qty'];}}return $t;
}
```

**Issues:** No blanks, cryptic `$t`/`$i`, single 80-char block.

**After (CBS ~0.7)**

```php
public function getActiveTotal(Collection $items): float
{
    return $items
        ->filter(fn (Item $i) => $i->isActive())
        ->map(fn (Item $i) => $i->price() * $i->quantity())
        ->sum();
}
```

**Improvements:** Blank lines between logical units, descriptive names, short blocks.

---

## Example 2: Nested conditionals → Match + early return

**Before (CBS ~0.2, LCF high)**

```php
function status($s){if($s===1){return 'new';}elseif($s===2){return 'active';}elseif($s===3){return 'done';}else{return 'unknown';}}
}
```

**After (CBS ~0.65)**

```php
function status(Status $s): string
{
    return match ($s->value()) {
        1 => 'new',
        2 => 'active',
        3 => 'done',
        default => 'unknown',
    };
}
```

**Improvements:** Declarative, each case on own line, no nesting.

---

## Example 3: Long identifiers → Balanced names

**Before (IRS low — too short)**

```php
$d = $u->getD();
$t = $o->getT();
$q = $i->getQ();
return $d * $t * $q;
```

**After (IRS good)**

```php
$discount = $user->discount();
$total = $order->total();
$quantity = $item->quantity();

return $discount * $total * $quantity;
```

**Before (IRS low — too long)**

```php
$firstOperandValueForCalculation = $this->getFirstOperandValueForCalculation();
$secondOperandValueForCalculation = $this->getSecondOperandValueForCalculation();
```

**After (IRS good)**

```php
$first = $this->firstOperand();
$second = $this->secondOperand();
```

**Sweet spot:** 3–15 chars for variables, 5–20 for methods.

---

## Example 4: Wall of functions → Air between blocks

**Before (VBI &lt; 0.05)**

```php
function a(){return 1;}
function b(){return 2;}
function c(){return 3;}
function d(){return 4;}
// ... 20 more
```

**After (VBI ~0.25)**

```php
function a(): int
{
    return 1;
}

function b(): int
{
    return 2;
}

function c(): int
{
    return 3;
}
```

**Rule:** One blank line between top-level declarations.

---

## Example 5: Controller action — dense → breathable

**Before (CBS ~0.2)**

```php
public function store(Request $r){$v=Validator::make($r->all(),['name'=>'required','email'=>'email']);if($v->fails()){return back()->withErrors($v);}$u=User::create($r->only(['name','email']));return redirect()->route('users.show',$u);}
```

**After (CBS ~0.6)**

```php
public function store(StoreUserRequest $request): RedirectResponse
{
    $user = User::create($request->validated());

    return redirect()
        ->route('users.show', $user)
        ->with('success', 'User created.');
}
```

**Improvements:** Validation moved to FormRequest, fluent chain with line breaks, blank between create and return.

---

## Example 6: Service method — extraction

**Before (single 40-line block, COL low)**

```php
public function process(Order $o): void
{
    $o->validate();
    $items=$o->items();
    foreach($items as $i){
        $p=$i->product();
        $s=$p->stock();
        if($s<$i->qty()){throw new InsufficientStockException();}
        $s-=$i->qty();
        $p->updateStock($s);
        $this->inventory->log($p,-$i->qty());
    }
    $o->markProcessed();
    $this->notifyWarehouse($o);
    $this->sendConfirmation($o);
}
```

**After (short blocks, CBS ~0.65)**

```php
public function process(Order $order): void
{
    $order->validate();

    $this->reserveStock($order);
    $order->markProcessed();

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

**Improvements:** Blank lines between logical phases, extracted method, descriptive names.

---

## Quick reference: What to change

| Low metric | Fix |
|------------|-----|
| **VBI** | Add blank lines between functions, classes, logical blocks |
| **COL** | Shorten blocks (extract methods), add blanks |
| **IRS** | Rename `$d`→`$discount`, shorten `$firstOperandValueForCalculation`→`$first` |
| **CBS** | Combine above; reduce WCD (split dense lines), reduce LCF (reduce nesting) |

---

*Run `php soda quality src` to see your current breathing scores.*
