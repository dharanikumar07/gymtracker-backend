# GymOS Login & Authentication System

This document outlines the authentication flow implemented in the GymOS backend and frontend.

## 1. Backend Authentication (Laravel Sanctum)

The backend uses **Laravel Sanctum** to provide token-based authentication for the API.

### Routes (`routes/api.php`)
- `POST /api/register`: Creates a new user and returns a Bearer token.
- `POST /api/login`: Authenticates user credentials and returns a Bearer token.
- `GET /api/me`: Returns the currently authenticated user with their associated fitness profile and expense tracker data. (Protected by `auth:sanctum` middleware).
- `POST /api/logout`: Revokes the user's current token.

### Controllers (`app/Http/Controllers/Api/Auth/AuthController.php`)
- **Login**: Validates credentials using `Auth::attempt`. If successful, generates a new Personal Access Token via `$user->createToken('auth_token')->plainTextToken`.
- **Me**: A critical endpoint that ensures the frontend always has the latest user state (UUID, name, email, and joined tables).

## 2. Frontend Persistence (React Context + LocalStorage)

To ensure the user stays logged in across page reloads, the frontend employs a dual strategy:

### AuthContext (`src/context/AuthContext.jsx`)
- **Persistence**: On application mount, the context checks `localStorage` for an `access_token`.
- **Verification**: If a token is found, it immediately calls the `/api/me` endpoint to verify the token and fetch the full user object.
- **Global State**: The `user` object is stored in a global React context, making it accessible to any component (like Onboarding or Dashboard) via the `useAuth()` hook.

### API Interceptor (`src/lib/api.js`)
- **Auto-Authorization**: An Axios interceptor automatically attaches the `Authorization: Bearer <token>` header to every request if a token exists in `localStorage`.
- **Session Expiry**: If the backend returns a `401 Unauthorized` (token expired), the interceptor automatically clears `localStorage` and redirects the user to the login page.

## 3. Onboarding Flow
- Onboarding is consolidated into a single final step (`POST /api/onboarding/complete`).
- This saves all steps (Profile, Routine, and Expenses) in a single atomic transaction.
- Expenses are stored in a dedicated `expense_trackers` table for better database normalization.

## 4. Key Security Features
- **UUIDs**: Users are identified by UUIDs in the frontend to prevent exposing auto-incrementing database IDs.
- **CORS**: Configured via `.env` to only allow requests from authorized frontend origins (e.g., `http://localhost:5174`).
