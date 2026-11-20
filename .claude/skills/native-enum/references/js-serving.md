# Serving enums to JavaScript

The package ships a Blade component (`<x-m2-enums::serve />`) that emits a `window.m2_ServedEnums` object containing the enums you register in `config/enums.php`. It's the simplest way to give a Vue/React/Alpine frontend enum values and labels without an API round-trip.

## Setup

1. Publish the config (one-time). This is **required**: the component reads `config('enums.serve')` and the package registers no default, so rendering it without the published config errors.

   ```bash
   php artisan vendor:publish --provider="mindtwo\NativeEnum\EnumServiceProvider"
   ```

   This creates `config/enums.php` (and also publishes the stubs and validation translations, since the config has no tag of its own).

2. Register the enums you want exposed:

   ```php
   // config/enums.php
   return [
       'serve' => [
           \App\Enums\UserRole::class,
           \App\Enums\OrderStatus::class,
           \App\Domain\Billing\InvoiceStatus::class,
       ],
   ];
   ```

3. Render the component once in your layout, before the scripts that read it:

   ```blade
   <!DOCTYPE html>
   <html>
   <head>…</head>
   <body>
       {{-- page content --}}

       <x-m2-enums::serve />
       {{-- app scripts --}}
   </body>
   </html>
   ```

## What gets emitted

The component iterates `config('enums.serve')` and calls `asServableEnum()` on each. Every enum is an **object keyed by case value**, not an array:

```js
window.m2_ServedEnums = {
  UserRole: {
    10: { name: 'ADMIN',    value: 10, localized_name: 'Administrator' },
    50: { name: 'CUSTOMER', value: 50, localized_name: 'Customer' }
  },
  OrderStatus: {
    10: { name: 'DRAFT',     value: 10, localized_name: 'Draft' },
    20: { name: 'PUBLISHED', value: 20, localized_name: 'Published' }
  }
};
```

Exception: an enum whose values are consecutive ints starting at `0` is emitted as a plain array. `Object.values()` handles both shapes, so always go through it when iterating.

Each entry has:

- `name`: the case name (`'ADMIN'`). Stable across locales.
- `value`: the primitive `->value`. Always use this for matching; never match on the localized name.
- `localized_name`: the translated label for the current request locale, or the raw case name if no translation exists.

## Use in JavaScript

### Look up a label

```js
const label = window.m2_ServedEnums.UserRole[10]?.localized_name;
// → 'Administrator'

// shape-independent alternative
Object.values(window.m2_ServedEnums.UserRole).find(e => e.value === 10)?.localized_name;
```

### Build a `<select>` (Alpine example)

```html
<select x-model="role">
    <template x-for="entry in Object.values(m2_ServedEnums.UserRole)" :key="entry.value">
        <option :value="entry.value" x-text="entry.localized_name"></option>
    </template>
</select>
```

### Build a `<select>` (Vue 3 example)

Vue templates cannot read arbitrary `window` globals, so pull the list into the component first:

```vue
<script setup>
import { ref } from 'vue';

const roles = Object.values(window.m2_ServedEnums.UserRole);
const role = ref(roles[0].value);
</script>

<template>
  <select v-model.number="role">
    <option v-for="entry in roles" :key="entry.value" :value="entry.value">
      {{ entry.localized_name }}
    </option>
  </select>
</template>
```

### Build a `<select>` (React example)

```tsx
function RoleSelect({ value, onChange }) {
  const options = Object.values(window.m2_ServedEnums.UserRole);
  return (
    <select value={value} onChange={(e) => onChange(Number(e.target.value))}>
      {options.map((o) => (
        <option key={o.value} value={o.value}>{o.localized_name}</option>
      ))}
    </select>
  );
}
```

⚠️ Watch out: enum values are ints, but `<select>` reads them as strings. Convert on read (`Number(e.target.value)` or `parseInt(...)`) before sending to the server, or rely on the `EnumValue` validation rule's non-strict mode to accept the string.

## Custom endpoint

If the global `<script>` injection doesn't fit (e.g. you don't want a global, or you want to fetch enums lazily), expose your own route:

```php
// routes/web.php or routes/api.php
use App\Enums\UserRole;
use App\Enums\OrderStatus;

Route::get('/api/enums', function () {
    return response()->json([
        'UserRole'    => UserRole::asServableEnum(),
        'OrderStatus' => OrderStatus::asServableEnum(),
    ]);
});
```

`asServableEnum()` is the underlying helper — it works without the config file or the component. The JSON has the same keyed-by-value shape as above.

## Loading only some enums per page

The component takes no props and always serves everything listed under `serve`. The package has no lazy-loading mechanism. To keep the global payload small, list only the enums every page needs and fetch the others through a custom endpoint as shown above.

## Gotchas

- **The served enum is an object, not an array.** `.map()` / `.find()` / `.length` directly on `m2_ServedEnums.UserRole` fail. Use `Object.values()`.
- **`window.m2_ServedEnums` is rendered server-side.** Whatever locale is active when the page is served is the locale you'll get. SPA route changes that don't re-render the layout will keep the original locale until full reload.
- **Everything served is public.** Case names, values and labels are visible to anyone who can load the page. If an enum is internal, don't list it — use a custom endpoint with auth.
- **Namespacing:** the component uses the enum's short class name as the JS object key. Two enums with the same short name in different namespaces will collide (`App\Enums\Status` and `App\Domain\Billing\Status` both become `Status`). Use unique short names or the custom-endpoint approach.
