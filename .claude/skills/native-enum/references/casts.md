# Eloquent casts

Two cast classes ship with the package: `EnumCast` (single case) and `EnumCollectionCast` (list of cases). You never reference them directly — you cast to the enum class and select the behaviour with modifiers.

## Requirement: `implements Castable`

Laravel only calls the trait's `castUsing()` when the enum implements `Illuminate\Contracts\Database\Eloquent\Castable`. The interface is **required**, not optional:

```php
use Illuminate\Contracts\Database\Eloquent\Castable;
use mindtwo\NativeEnum\BaseEnum;

enum UserRole: int implements Castable
{
    use BaseEnum;
    // …
}
```

Without it:

- `'role' => UserRole::class` is handled by Laravel's built-in enum cast, not by this package (different null and strictness behaviour).
- `:nullable` and `:collection` fail with `Cannot instantiate enum`, because Laravel tries to `new` the enum as a cast class.

## `EnumCast` — single value

```php
protected $casts = [
    'role' => UserRole::class,
];
```

| Raw column value | `$model->role`                                   |
| ---------------- | ------------------------------------------------ |
| `NULL`           | throws `NotNullableEnumField` (unless `:nullable`) |
| `10` (int)       | `UserRole::ADMIN`                                |
| `'10'` (string)  | throws `ItemNotFoundException` (strict `===`)    |
| `999`            | throws `ItemNotFoundException` (unknown case)    |

There is **no type coercion**, neither on read nor on write. Match the column type to the backing type: an integer column for an int-backed enum, a string column for a string-backed one.

### `:nullable` modifier

```php
protected $casts = [
    'role' => UserRole::class.':nullable',
];
```

Allows `NULL`. Without the modifier, `set()` with `null` and `get()` with `null` both throw `mindtwo\NativeEnum\Exceptions\NotNullableEnumField`.

## `EnumCollectionCast` — collections

```php
protected $casts = [
    'roles' => UserRole::class.':collection',
];
```

| Raw column value   | `$model->roles`                         |
| ------------------ | --------------------------------------- |
| `NULL`             | throws `NotNullableEnumField` (unless `:nullable`) |
| `'[10,50]'` (JSON) | `[UserRole::ADMIN, UserRole::CUSTOMER]` |

The attribute is a **plain PHP array** of cases, not a `Collection`. The default storage format is JSON (`[10,50]`).

### `:comma` storage format

```php
protected $casts = [
    'tags' => UserRole::class.':collection,comma',
];
```

For `VARCHAR`/`TEXT` columns. The package serializes to `"10,50"` and reads back, trimming whitespace and converting the parts to ints for int-backed enums. An empty string reads as `[]`. Parts that aren't valid ints (`1abc`, `abc`) are not coerced and throw `ItemNotFoundException`. A lone `0` reads as the zero case if the enum has one, and as `[]` otherwise. **Caveats**:

- Comma storage is intentionally unescaped. String-backed values must not be empty, contain a comma, or have leading or trailing whitespace. Writing one of these values throws `InvalidArgumentException`; use the default JSON format instead.
- Not searchable without `LIKE` matching. For real querying, use JSON.

### Combining modifiers

```php
protected $casts = [
    'roles' => UserRole::class.':collection,nullable',
    'tags'  => UserRole::class.':collection,comma,nullable',
];
```

Modifier order doesn't matter — `:collection,comma,nullable` and `:nullable,comma,collection` are equivalent. List `collection` first for readability.

## Read vs write semantics

```php
$user = User::find(1);

$user->role = UserRole::CUSTOMER;  // assigns case → stores 50
$user->role = 50;                  // raw value of the exact backing type → stores 50
$user->role = '50';                // throws ItemNotFoundException (string !== int)
$user->role = OtherEnum::Foo;      // only accepted if Foo's value is a UserRole value; throws otherwise
$user->save();

$user->role;                       // UserRole::CUSTOMER
```

Request input is always a string, so convert before assigning:

```php
$user->role = UserRole::from((int) $request->input('role'));
```

For collections:

```php
$user->roles = [UserRole::ADMIN, UserRole::CUSTOMER]; // cases → stores [10,50]
$user->roles = [10, 50];                               // raw values → stores [10,50]
$user->roles = UserRole::ADMIN;                        // single case is wrapped → stores [10]
$user->roles = collect([10, 50]);                      // throws ItemNotFoundException
$user->roles = collect([10, 50])->all();               // works
```

`set()` uses `Arr::wrap()`, which passes arrays through and wraps everything else — including a `Collection` — into a one-element array. Always assign a plain array.

## Migration patterns

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->unsignedTinyInteger('role');   // int-backed enum
    // or
    $table->string('role', 32);            // string-backed enum
    $table->json('roles');                 // :collection
    // or
    $table->string('roles', 255);          // :collection,comma (small sets only)
});
```

Add `->nullable()` only together with the `:nullable` modifier. If the enum uses large `int` values, use `unsignedBigInteger`. If the values fit in a byte (`0..255`), `TINYINT` is enough.

## Common errors

| Error                                                      | Cause                                                        | Fix                                                        |
| ---------------------------------------------------------- | ------------------------------------------------------------ | ---------------------------------------------------------- |
| `Cannot instantiate enum App\Enums\UserRole`               | `:nullable` / `:collection` used, enum lacks `Castable`      | Add `implements Castable` to the enum                      |
| `NotNullableEnumField: Field X on model Y is not nullable` | Column is `NULL` (or `null` assigned) without `:nullable`    | Add `:nullable`                                            |
| `ItemNotFoundException` on read                            | Column holds a value that is not a case, or the wrong type   | Fix the data, or match the column type to the backing type |
| `ItemNotFoundException` on write                           | Assigned a string for an int-backed enum, or a `Collection`  | Convert first (`(int)`, `UserRole::from()`, `->all()`)     |
| `ItemNotFoundException` after changing a case's value      | Existing rows still hold the old value                       | Migrate the column to the new value                        |
| `Object of class MyEnum could not be converted to string`  | Using `implode` on cases directly                            | Project to `->value` first                                 |
