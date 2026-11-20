# Livewire integration

Livewire 3 hydrates backed enums natively, so an enum that uses `BaseEnum` can be a typed public property with no extra wiring. This works because of Livewire's own enum support — **not** because of the trait's `toLivewire()` / `fromLivewire()` helpers (see the last section).

## Basic usage

```php
namespace App\Livewire;

use App\Enums\UserRole;
use App\Models\User;
use Livewire\Component;

class UserForm extends Component
{
    public UserRole $role = UserRole::CUSTOMER;

    public function save(): void
    {
        User::create(['role' => $this->role]);
    }

    public function render()
    {
        return view('livewire.user-form');
    }
}
```

`$this->role` is always a `UserRole` case. Livewire sends the raw value (`50`) plus the enum class to the browser and rebuilds the case with `UserRole::from()` on the next request.

## Nullable enum properties

```php
public ?UserRole $role = null;
```

An empty string from the browser (for example a placeholder `<option value="">`) is hydrated as `null`.

## `wire:model` with a `<select>`

Use the primitive value as the option value. `asSelectArray()` gives `[value => label]`, with translated labels for localized enums:

```blade
{{-- resources/views/livewire/user-form.blade.php --}}
<div>
    <select wire:model="role">
        @foreach (\App\Enums\UserRole::asSelectArray() as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>

    <button wire:click="save">Save</button>
</div>
```

The browser sends the value as a string; Livewire converts it back with `UserRole::from()`.

## Lists of enums

Keep the raw values in the public property and convert where you need cases:

```php
class UserForm extends Component
{
    /** @var int[] */
    public array $roleValues = [];

    /** @return UserRole[] */
    public function roles(): array
    {
        return array_map(
            fn ($value) => UserRole::from((int) $value),
            $this->roleValues
        );
    }
}
```

## `toLivewire()` / `fromLivewire()`

The trait has two conversion helpers:

| Call                                               | Returns                               |
| -------------------------------------------------- | ------------------------------------- |
| `UserRole::ADMIN->toLivewire()`                    | `['value' => 10, 'label' => 'ADMIN']` |
| `UserRole::fromLivewire(UserRole::ADMIN)`          | `UserRole::ADMIN`                     |
| `UserRole::fromLivewire(['value' => 50])`          | `UserRole::CUSTOMER`                  |
| `UserRole::fromLivewire((object) ['value' => 50])` | `UserRole::CUSTOMER`                  |

`fromLivewire()` accepts a case, the array produced by `toLivewire()`, or an object with a `value` property. Livewire calls these methods only on classes that implement `Livewire\Wireable`, and it passes the array form back.

## Gotchas

- **`Wireable` is not needed.** Livewire's native enum handling covers typed enum properties; adding `implements Wireable` to an enum gains nothing. The helpers are for code that wants the `value`/`label` shape.
- **`toLivewire()`'s `label` is the raw case name** (`'ADMIN'`), not the localized one. For user-facing labels use `asSelectArray()` or `$case->name()`.
- **A value that is not a case makes `UserRole::from()` throw a `ValueError`** during hydration. Only render option values that come from the enum.
