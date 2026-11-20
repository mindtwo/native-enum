# Livewire

[← All documentation](index.md)

## Enum properties

Livewire 3 hydrates backed enums on its own, so an enum that uses `BaseEnum` can be a typed public property without any setup:

```php
namespace App\Livewire;

use App\Enums\UserRole;
use Livewire\Component;

class UserForm extends Component
{
    public UserRole $role = UserRole::CUSTOMER;
}
```

## Select options

`asSelectArray()` provides the options for a `<select>`, with translated labels if the enum is [localized](localization.md):

```blade
<select wire:model="role">
    @foreach (\App\Enums\UserRole::asSelectArray() as $value => $label)
        <option value="{{ $value }}">{{ $label }}</option>
    @endforeach
</select>
```

## `toLivewire()` and `fromLivewire()`

The trait also has two conversion helpers:

| Call | Returns |
| --- | --- |
| `UserRole::ADMIN->toLivewire()` | `['value' => 10, 'label' => 'ADMIN']` |
| `UserRole::fromLivewire(['value' => 50, 'label' => 'CUSTOMER'])` | `UserRole::CUSTOMER` |
| `UserRole::fromLivewire((object) ['value' => 50])` | `UserRole::CUSTOMER` |
| `UserRole::fromLivewire(UserRole::ADMIN)` | `UserRole::ADMIN` |

`label` is the case name, not the translated label.

Livewire calls these methods only on classes that implement `Livewire\Wireable`. An enum does not need that interface, because Livewire already handles backed enums as described above. The helpers are there for code that wants the `value` and `label` shape.

## Related

- [Helper methods](helpers.md): `asSelectArray()` and the other list helpers
- [Serving enums to JavaScript](javascript.md): enums for Vue, React or Alpine
