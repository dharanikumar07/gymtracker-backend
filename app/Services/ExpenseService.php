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
    public function storeBulk(array $expenses)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            foreach ($expenses as $expense) {
                $this->createExpenseCategory($expense, $user);
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
     * Log a daily expense OR set up a fixed category.
     */
    public function logExpense(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            $period = $data['expense_period'] ?? 'variable';
            $date = $data['expense_date'] ?? now()->toDateString();

            // 1. Find or create the category
            $category = ExpenseCategory::updateOrCreate(
                [
                    'user_uuid' => $user->uuid,
                    'category_type' => $data['category_type'],
                ],
                [
                    'expense_period' => $period,
                    'default_amount' => ($period === 'fixed') ? $data['amount'] : 0,
                ]
            );

            // 2. Log entry (Update by UUID if provided, else by User/Category/Name/Date)
            $logMatch = ['user_uuid' => $user->uuid, 'category_uuid' => $category->uuid, 'name' => $data['name'], 'expense_date' => $date];
            if (isset($data['uuid']) && !empty($data['uuid'])) {
                $logMatch = ['uuid' => $data['uuid'], 'user_uuid' => $user->uuid];
            }

            $log = ExpenseLog::updateOrCreate(
                $logMatch,
                [
                    'amount' => $data['amount'],
                    'notes' => $data['notes'] ?? null,
                ]
            );

            return [
                'category' => $category,
                'log' => $log
            ];
        });
    }

    private function createExpenseCategory(array $expense, $user)
    {
        ExpenseCategory::updateOrCreate(
            [
                'user_uuid' => $user->uuid,
                'category_type' => $expense['category_type'],
            ],
            [
                'uuid' => Str::uuid(),
                'expense_period' => $expense['expense_period'] ?? 'fixed',
                'default_amount' => $expense['default_amount'],
            ]
        );
    }
}
