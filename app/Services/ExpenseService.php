<?php

namespace App\Services;

use App\Models\ExpenseCategory;
use App\Models\ExpenseLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers\Helper;

class ExpenseService
{
    /**
     * Store bulk expense categories (Onboarding setup).
     */
    public function storeBulk(array $expenses, $planUuid = null)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            foreach ($expenses as $expense) {
                $this->createExpenseCategory($expense, $user, $planUuid);
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Helper::logError('Unable to store expense categories', [__CLASS__, __FUNCTION__], $e);
            throw $e;
        }
    }
    /**
     * Delete an expense category.
     */
    public function deleteExpenseCategory(string $uuid)
    {
        return DB::transaction(function () use ($uuid) {
            $user = Auth::user();
            $category = ExpenseCategory::where('uuid', $uuid)
                ->where('user_uuid', $user->uuid)
                ->firstOrFail();

            return $category->delete();
        });
    }

    public function createExpenseCategory(array $expense, $user, $planUuid = null)
    {
        return ExpenseCategory::updateOrCreate(
            [
                'user_uuid' => $user->uuid,
                'category_type' => Helper::slugifyCategory($expense['category_name']),
                'plan_uuid' => $planUuid,
            ],
            [
                'expense_period' => $expense['expense_period'] ?? 'fixed',
                'default_amount' => $expense['default_amount'],
            ]
        );
    }

    public function getFixedCommitments($user, $cycle)
    {
        $planUuid = $cycle ? $cycle->plan_uuid : null;

        return ExpenseCategory::where('user_uuid', $user->uuid)
            ->where('expense_period', 'fixed')
            ->when($planUuid, function ($query) use ($planUuid) {
                return $query->where('plan_uuid', $planUuid);
            })
            ->get()
            ->map(fn($cat) => [
                'uuid' => $cat->uuid,
                'name' => Helper::deslugifyCategory($cat->category_type),
                'amount' => (float)$cat->default_amount,
                'is_paid' => $cycle && ExpenseLog::where('plan_cycle_uuid', $cycle->uuid)
                    ->where('category_uuid', $cat->uuid)
                    ->exists()
            ]);
    }

    public function getDailyLogs($user, $date)
    {
        return ExpenseLog::where('user_uuid', $user->uuid)
            ->where('expense_date', $date)
            ->with('category')
            ->get()
            ->map(fn($log) => [
                'uuid' => $log->uuid,
                'name' => $log->name,
                'amount' => (float)$log->amount,
                'category_name' => Helper::deslugifyCategory($log->category->category_type ?? 'Other'),
            ]);
    }
}
