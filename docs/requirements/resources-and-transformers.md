# Resources & Transformers

## Purpose
- Decouple Eloquent models from API responses
- Stable, versioned contract

## Location & Naming
- `app/Http/Resources/{FeatureName}/{FeatureName}{Action}Resource.php`
  - Example: `app/Http/Resources/User/UserShowResource.php`

## Resource Example
```php
<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => (int) $this->id,
            'name'       => (string) $this->name,
            'email'      => (string) $this->email,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}```

## Controller Usage Example

```php
use App\Http\Resources\User\UserResource;

public function showUser(StoreRequest $request)
{
    // ...

    return UserResource::collection($users);
}```
