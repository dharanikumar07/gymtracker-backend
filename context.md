# Expense Module Analysis & Implementation Plan

## Analysis of Current State
- **Backend:**
  - `ExpenseController` and `ExpenseService` already exist.
  - Logging an expense currently uses `updateOrCreate` for categories and `create` for logs.
  - Basic transaction support in `ExpenseService`.
  - Date-based logging is supported in the database but not fully utilized in the frontend.
- **Frontend:**
  - Single `Expenses` page with an overview and a log form.
  - No date picker or week-view similar to `TrackDiet`.
  - UI is functional but not strictly adhering to the "two-column section 1" and "daily log" requirements.

## Proposed Changes
### Backend (Laravel)
- Update `ExpenseController@log` to handle both fixed and variable expenses via a single POST route.
- Refactor `ExpenseService@logExpense` to:
  - Use `DB::beginTransaction()`, `DB::commit()`, `DB::rollBack()`.
  - Use `updateOrCreate` for both categories and logs where applicable (for daily logs, it should probably update if an entry with the same name/category/date exists).
  - Minimize `if` statements.
  - No PATCH routes for updates.

### Frontend (React)
- **Section 1:**
  - Divided into two columns:
    - Left: Fixed Expenses list.
    - Right: Current Month Dynamic Spend (Summary).
- **Section 2:**
  - Add Expense Card:
    - Fields: `name`, `type` (category), `amount`.
    - Selection for "Fixed" or "Variable".
- **Section 3 (Log Stream):**
  - Implement a daily log view similar to `TrackDiet` with date selection and week navigation.
  - Allow viewing/editing logs for specific days.

### Implementation Logic Details
- Use `updateOrCreate` in the backend service.
- Ensure the frontend sends the selected date when logging.
- Improve UI with "neat and clean" design elements.
