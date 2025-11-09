# Creating API Controllers (Laravel)

This guide standardizes how we add new HTTP APIs.

## Path & Naming
- Controllers live at: `app/Http/Controllers/Api/v1/{FeatureName}/{FeatureName}Controller.php`
- Requests live at: `app/Http/Requests/{FeatureName}/{FeatureName}{Action}Request.php`
- Resources live at: `app/Http/Resources/{FeatureName}/{FeatureName}{Action}Resource.php`

## Principles
- **Single responsibility** per controller method.
- **No inline validation** — always use Form Requests.
- **Transform output via Resources** — never return raw models.
- **Return response codes with Illuminate\Http\Response** - never specify 201 or 404 in numbers.
- **PSR-12** coding style, typed params/returns.
- **Swagger Documentation** - always use Swagger documentation for all API controller endpoints.
- **Pagination & filtering** — list endpoints must follow [Pagination, Sorting & Filtering](./pagination-in-controllers.md).

## Controller Skeleton
```php
<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\User\UserShowResource;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::query()->latest()->paginate();
        return response()->json([
            'data' => UserShowResource::collection($users),
            'meta' => ['page' => $users->currentPage(), 'total' => $users->total()],
        ]);
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        $user = User::create($request->validated());
        return response()->json(['data' => new UserShowResource($user)], Response::HTTP_CREATED);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json(['data' => new UserShowResource($user)]);
    }

    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());
        return response()->json(['data' => new UserShowResource($user)]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
```
