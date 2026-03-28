# Backend API Rules (Laravel)

## 📌 Purpose

This document defines the **standard API structure and coding rules** for all backend implementations.
All APIs must strictly follow this structure for consistency, error handling, and maintainability.

---

## ✅ 1. Controller Method Structure

All controller methods **must follow this exact structure**:

```php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

public function sampleFunction(ValidationClass $request)
{
    DB::beginTransaction();

    try {
        // ✅ Business Logic Here

        DB::commit();

        return Response::json([
            'message' => 'Success'
        ], HttpFoundationResponse::HTTP_OK);

    } catch (\Exception $e) {
        DB::rollback();

        Helper::logError(
            'Error occurred in sampleFunction',
            [__CLASS__, __FUNCTION__],
            $e,
            $request->toArray()
        );

        return Response::json([
            'message' => 'Server Error Occurred'
        ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}
```

---

## ✅ 2. Mandatory Rules

### 🔹 Transactions

* Always wrap logic inside:

  ```php
  DB::beginTransaction();
  DB::commit();
  DB::rollback();
  ```

---

### 🔹 Try-Catch חובה (Mandatory)

* Every API must use `try-catch`
* No exceptions should be left unhandled

---

### 🔹 Error Logging

* Always log errors using:

  ```php
  Helper::logError()
  ```
* Required parameters:

  * Message
  * Class & Function
  * Exception object
  * Request payload

---

### 🔹 Response Format

* Always return JSON using:

  ```php
  Response::json()
  ```

---

## ✅ 3. Standard Responses

### ✔️ Success Response

```php
return Response::json([
    'message' => 'Success'
], HttpFoundationResponse::HTTP_OK);
```

---

### ❌ Error Response

```php
return Response::json([
    'message' => 'Server Error Occurred'
], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
```

---

## ✅ 4. Validation नियम (Rules)

* Always use a **Validation Class**
* Do NOT use inline validation in controller
* Example:

  ```php
  public function store(StoreRequest $request)
  ```

---

## ✅ 5. Clean Code Guidelines

* No business logic outside `try` block
* Keep controllers thin
* Use services if logic is complex
* Use meaningful function names

---

## ❌ 6. What NOT to Do

* ❌ Do not return raw arrays
* ❌ Do not skip transactions
* ❌ Do not skip error logging
* ❌ Do not expose exception messages in API
* ❌ Do not use `echo`, `print`, or `dd()`

---

## 🚀 Usage Instruction for AI Agents

Whenever generating backend code:

> "Follow backend-rules.md strictly. Use the defined API structure, transaction handling, and response format."

---

## 🏆 Goal

* Consistent API structure
* Better debugging
* Production-ready backend
* Clean and scalable codebase

---
## !!!!important 

for get api i did not want to add this three calls

  ```php
  DB::beginTransaction();
  DB::commit();
  DB::rollback();
  ```
