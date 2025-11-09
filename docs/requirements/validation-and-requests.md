# Validation & Form Requests

## Why Form Requests
- Centralized validation
- Authorization hook (`authorize()`)
- Cleaner controllers

## Location & Naming
- `app/Http/Requests/{FeatureName}/{FeatureName}{Action}Request.php`
  - Example: `app/Http/Requests/User/UserStoreRequest.php`

## StoreRequest Example
```php
<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or add policy checks
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'That email is already in use.',
        ];
    }
}```

## UpdateRequest Example

```php
class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('user')->id ?? null;

        return [
            'name'  => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', "unique:users,email,{$id}"],
        ];
    }
}```

## Controller Usage Example

```php
public function store(StoreRequest $request)
{
    $data = $request->validated();
    // ...
}```
