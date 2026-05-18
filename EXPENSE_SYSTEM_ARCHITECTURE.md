# Budget & Expense Tracking System Architecture

This document explains the idempotent budget cycle architecture designed for high-integrity financial tracking.

## 1. Database Schema Design

The system utilizes three core tables, with a focus on "Snapshots" to maintain historical accuracy while allowing real-time flexibility.

### A. `expense_categories`
- **Purpose:** Template for recurring and variable costs.
- **Key Columns:**
    - `plan_uuid`: Isolates expenses to a specific budget strategy.
    - `expense_period`: `fixed` (committed costs) or `variable` (daily logs).
    - `default_amount`: The baseline cost used for cycle snapshots.

### B. `budget_plan_cycles` (The Idempotent Snapshot)
- **Composite Unique Identification:** A cycle is uniquely defined by the combination of `user_uuid`, `plan_uuid`, `cycle_start`, and `cycle_end`.
- **Methodology:** The system uses `updateOrCreateCycle` to ensure that for any given timeframe and plan, only **one record of truth** exists.
- **Calculated Columns:** 
    - `fixed_expense_total`: A snapshot of all fixed commitments at the time of cycle generation.
    - `variable_expense_total`: Cumulative total of manual `expense_logs`.
    - `remaining_amount`: `budget_amount - (fixed_total + variable_total)`.
- **States:** `active`, `paused`, `completed`, `terminated`.

### C. `expense_logs` (The Transactions)
- Every transaction is immutable and linked to a `plan_cycle_uuid`.
- This ensures historical integrity: logs stay tied to the specific budget cycle (and the macro-economic snapshot of that time) even if the user changes plans later.

---

## 2. Core Logic & Implementation Strategies

### A. Idempotent Cycle Generation (`updateOrCreateCycle`)
Unlike standard creation logic, this system uses an idempotent approach to prevent "Ghost" entries or duplicate active cycles.
- **Keys:** `user_uuid`, `plan_uuid`, `cycle_start`, `cycle_end`.
- **State Preservation:** When a cycle is updated, existing `variable_expense_total` is preserved to ensure that manual logs are never lost.
- **Advantages:** 
    - Eliminates redundant "Termination" logic for accidental re-triggers.
    - Allows the system to "self-repair" if a user updates plan metadata (like total budget amount) mid-cycle.

### B. Scalable Auto-Generation ("Ending Tomorrow" Rule)
The system proactively generates the next cycle 24 hours before the current one expires.
- **Workflow:** 
    1. A daily scheduler identifies active cycles ending tomorrow.
    2. It dispatches a `GenerateNextCycleJob`.
    3. The job triggers `updateOrCreateCycle` for the next range.
    4. **Fixed Expense Snapshot:** The system fetches the *current* state of fixed expenses for that specific plan and locks them into the new cycle.

### C. The Re-Sync Logic (`recalculateCurrentFixedExpenses`)
If a user adds or removes a fixed expense category *after* a cycle has already started:
1. The system identifies the **currently active cycle** for the user.
2. It re-scans the `expense_categories` table for that specific `plan_uuid`.
3. It refreshes the `fixed_expense_total` and updates the `fixed_snapshot` in the cycle's `meta_data`.
4. **Result:** The `remaining_amount` is adjusted in real-time without affecting past `completed` cycles.

### D. Plan Transitions & Status Management
- **Switching Plans:** When a new plan is activated, `createInitialCycle` triggers `terminateActiveCycle`. This truncates the current cycle's `cycle_end` to `new_plan_start - 1 day` and sets status to `terminated`.
- **Activation Toggle:** Status is synchronized via a join with the `plans` table's `is_active` flag. Paused plans result in `paused` cycles, which can be resumed if they haven't expired.

---

## 3. Financial Consistency Principles

1. **Snapshots over Joins:** We store the `fixed_expense_total` within the cycle record. This ensures that changing a price (e.g., "Rent") today does not retroactively change your reported expenses for a cycle that ended last month.
2. **Transaction Integrity:** All logging and status changes use `DB::transaction`.
3. **Boundary Protection:** Middleware prevents logging variable expenses if `resolveActiveCycle()` returns null (meaning no plan is currently active or established for today).

---

## 4. Scalability & Performance
- **Indexed Lookups:** Heavily utilizes composite indexes on `(cycle_end, status)` and `(user_uuid, plan_uuid, cycle_start)`.
- **Resource Standardization:** All frontend communication uses Laravel API Resources (e.g., `ExpenseResource`) to ensure a predictable and type-safe data contract.
- **Chunked Processing:** Scheduler operations use `chunkById` to handle high user volume without memory exhaustion.
