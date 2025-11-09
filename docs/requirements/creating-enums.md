# Creating Enums (Laravel)

This guide standardizes how we add and use enums across the codebase.

## Path & Naming
- **Path:** `app/Enums/{FeatureName}/{EnumName}.php`, `Rules\Enum\{Feature}/{EnumName}Rule.php`
  - Examples:
    - `app/Enums/AiChat/ConversationUserRole.php`
    - `app/Enums/Billing/InvoiceStatus.php`
    - `app/Rules/Enum/AiChat/ConversationUserRoleRule.php`
- **Namespace:** `App\Enums\{FeatureName}`
- **Naming:**
  - **Singular, PascalCase** (e.g., `InvoiceStatus`, not `InvoiceStatuses`).
  - Prefer **native backed enums** (PHP ≥8.1). Use **string-backed** by default.
  - Avoid the `Enum` suffix unless needed to disambiguate (e.g., `Type` vs `FileType`).

## Principles
- **Type-safety first**: use native enums instead of string constants.
- **Single source of truth**: rely on `cases()`; never duplicate values in arrays.
- **Behavior co-located**: put enum-specific behavior (e.g., `label()`, `isTerminal()`) on the enum.
- **Framework integration**: always wire enums into Eloquent casts and validation rules.
- **Storage clarity**: default to `VARCHAR` + `CHECK` constraints (or a lookup table); avoid MySQL `ENUM` unless required.
- **PSR-12**: typed params/returns, strict comparisons.

## When To Use Enums
- Finite, well-defined sets (status, roles, types, modes).
- Values used in validation rules and model attributes.
- Branching logic that benefits from exhaustive `match` handling.

## Enum Skeleton
```php
<?php

namespace App\Enums\AiChat;

enum ConversationUserRole: string
{
    case OWNER = 'owner';
    case COLLABORATOR = 'collaborator';
    case VIEWER = 'viewer';

    /** Human-readable label for UI */
    public function label(): string
    {
        return match ($this) {
            self::OWNER        => __('Owner'),
            self::COLLABORATOR => __('Collaborator'),
            self::VIEWER       => __('Viewer'),
        };
    }

    /** Whether the role has write perms */
    public function canWrite(): bool
    {
        return match ($this) {
            self::OWNER, self::COLLABORATOR => true,
            self::VIEWER => false,
        };
    }

    /** Whether the role is an admin/manager */
    public function canManage(): bool
    {
        return $this === self::OWNER;
    }

    /** Convenience helpers */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function isValid(string $value): bool
    {
        return self::tryFrom($value) !== null;
    }
}
```

## Laravel Integration
### Eloquent Casts
```php
// In your Eloquent model
use App\Enums\AiChat\ConversationUserRole;

protected $casts = [
    'role' => ConversationUserRole::class, // DB column is string
];
```
Usage:
```php
if ($userConversation->role->canWrite()) { /* ... */ }
```

### Validation
```php
use Illuminate\Validation\Rules\Enum;
use App\Enums\AiChat\ConversationUserRole;

$request->validate([
    'role' => ['required', new Enum(ConversationUserRole::class)],
]);
```

### Factories/Seeders
```php
use App\Enums\AiChat\ConversationUserRole as Role;

UserConversation::factory()->create([
    'role' => Role::OWNER, // Cast handles persistence
]);
```

## Database Schema
Prefer **string** + **CHECK** (portable, evolvable):
```php
Schema::table('conversation_users', function (Blueprint $t) {
    $t->string('role', 24);
});

// PostgreSQL check (example)
DB::statement("ALTER TABLE conversation_users
  ADD CONSTRAINT conversation_users_role_check
  CHECK (role IN ('owner','collaborator','viewer'))");
```
**MySQL**: emulate with triggers or rely on app-level validation. Avoid `ENUM` unless operationally required.

## OpenAPI / Swagger (Optional but Recommended)
Describe enum values for API docs:
```php
/**
 * @OA\Schema(
 *   schema="ConversationUserRole",
 *   type="string",
 *   enum={"owner","collaborator","viewer"}
 * )
 */
```
Then reference the schema in request/response models.

## Testing
- **Unit**: behavior methods (`canWrite`, `canManage`, `label`) and `tryFrom` coverage.
- **Feature**: validation rejects unknown values; model cast reads/writes correctly.

```php
it('validates enum values', function () {
    expect(ConversationUserRole::isValid('owner'))->toBeTrue();
    expect(ConversationUserRole::isValid('random'))->toBeFalse();
});
```

## Patterns & Conventions
- **Labels & Options for UI**
  ```php
  /** @return array<string,string> value => label */
  public static function options(): array
  {
      $pairs = [];
      foreach (self::cases() as $case) {
          $pairs[$case->value] = $case->label();
      }
      return $pairs;
  }
  ```
  Use in selects: `Form::select('role', ConversationUserRole::options())`.

- **Policies/Gates**: keep high-level authorization in Policies; call enum helpers inside.

- **No Magic Strings**: never compare against raw strings—use `ConversationUserRole::OWNER`.

- **Exhaustive `match`**: prefer `match` over `if` trees to make additions compile-time visible.

## Quick Checklist
- [ ] File at `app/Enums/{Feature}/{EnumName}.php`, singular, PascalCase.
- [ ] **String-backed** native enum; values are stable API-facing strings.
- [ ] Add behavior (`label()`, predicates like `isTerminal()`), no duplicated arrays.
- [ ] Cast in models; add request validation with `Rules\Enum\{Feature}/{EnumName}Rule.php`.
- [ ] DB column `VARCHAR` + `CHECK` (or lookup table); avoid MySQL `ENUM`.
- [ ] Document values in OpenAPI.
- [ ] Unit tests for behavior and validation.

---

**TL;DR**: Use native, string-backed enums with behavior; wire into casts + validation; store as strings with constraints; keep logic in the enum and authorization in policies.

