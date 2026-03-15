<?php

namespace App\Data\DietPlanData;

abstract class AbstractDietPlan implements DietPlanInterface
{
    protected string $dietType; // 'veg' or 'nonveg'
    
    public function __construct(string $dietType)
    {
        $this->dietType = $dietType;
    }

    abstract protected function getCalorieAdjustment(): int;
    abstract protected function getMealTemplates(): array;

    public function generate(float $weight): array
    {
        // 1. Establish Absolute Targets
        $maintenanceCalories = $weight * 2.2 * 14;
        $targetCalories = $maintenanceCalories + $this->getCalorieAdjustment();
        
        if ($targetCalories < 1200) $targetCalories = 1200;

        $proteinGrams = $weight * 2; // Exact Protein Rule
        $proteinCalories = $proteinGrams * 4;
        $remainingCalories = $targetCalories - $proteinCalories;

        $carbsGrams = ($remainingCalories * 0.60) / 4; // Exact Carb Rule
        $fatsGrams = ($remainingCalories * 0.40) / 9;  // Exact Fat Rule

        $targets = [
            'calories' => (int) round($targetCalories),
            'protein' => (int) round($proteinGrams),
            'carbs' => (int) round($carbsGrams),
            'fats' => (int) round($fatsGrams),
        ];

        // 2. Build the Weekly Plan
        $templates = $this->getMealTemplates();
        $weeklyPlan = [];
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        foreach ($days as $index => $day) {
            $templateIndex = $index % count($templates);
            $dayTemplate = $templates[$templateIndex];
            
            $weeklyPlan[$day] = $this->balanceDay($dayTemplate, $targets);
        }

        return [
            'nutrition_targets' => $targets,
            'weekly_plan' => $weeklyPlan
        ];
    }

    /**
     * The Precision Engine: Balances a template to match targets exactly
     */
    protected function balanceDay(array $template, array $targets): array
    {
        // Flatten all items in the template for calculation
        $allItems = [];
        foreach ($template as $mealType => $items) {
            foreach ($items as $item) {
                $item['meal_type'] = $mealType;
                $allItems[] = $item;
            }
        }

        // 1. Initial rough scale based on calories
        $baseCals = 0;
        foreach ($allItems as $item) {
            $baseCals += $item['base_calories'];
        }
        
        $scaleFactor = $baseCals > 0 ? ($targets['calories'] / $baseCals) : 1;

        foreach ($allItems as &$item) {
            $item['quantity'] = $item['base_quantity'] * $scaleFactor;
            $this->recalculateItem($item);
        }
        unset($item); // Critical: Break the reference

        // 2. Precision Balancing Loop
        for ($i = 0; $i < 3; $i++) {
            $this->adjustMacro($allItems, 'protein', $targets['protein']);
            $this->adjustMacro($allItems, 'carbs', $targets['carbs']);
            $this->adjustMacro($allItems, 'fats', $targets['fats']);
        }

        // 3. Final cleanup and re-grouping
        $balancedPlan = ['breakfast' => [], 'lunch' => [], 'dinner' => [], 'snack' => []];
        foreach ($allItems as $finalItem) {
            $mType = $finalItem['meal_type'] ?? 'snack';
            
            // Remove calculation keys for clean response
            $cleaned = [
                'food_name' => $finalItem['name'],
                'quantity' => (float) number_format($finalItem['quantity'], 2, '.', ''),
                'unit' => $finalItem['unit'],
                'calories' => (int) round($finalItem['calories']),
                'protein' => (int) round($finalItem['protein']),
                'carbs' => (int) round($finalItem['carbs']),
                'fats' => (int) round($finalItem['fats']),
            ];
            
            $balancedPlan[$mType][] = $cleaned;
        }

        return $balancedPlan;
    }

    /**
     * Adjusts a specific macro by modifying the "Anchor" food
     */
    protected function adjustMacro(array &$items, string $macro, float $targetGrams): void
    {
        $currentTotal = 0;
        foreach ($items as $item) {
            $currentTotal += $item[$macro];
        }
        
        $diff = $targetGrams - $currentTotal;
        if (abs($diff) < 0.5) return;

        $anchorIndex = -1;
        $maxDensity = -1;

        foreach ($items as $idx => $item) {
            if ($item['unit'] === 'pcs') continue; 
            
            $density = $item[$macro] / (max(1, $item['quantity']));
            if ($density > $maxDensity) {
                $maxDensity = $density;
                $anchorIndex = $idx;
            }
        }

        if ($anchorIndex === -1) $anchorIndex = 0;

        $anchor = &$items[$anchorIndex];
        $density = $anchor[$macro] / (max(1, $anchor['quantity']));
        
        if ($density > 0) {
            $additionalQty = $diff / $density;
            $anchor['quantity'] += $additionalQty;
            if ($anchor['quantity'] < 5) $anchor['quantity'] = 5; 
            $this->recalculateItem($anchor);
        }
        unset($anchor); // Break the reference
    }

    protected function recalculateItem(array &$item): void
    {
        $food = $this->getFoodByName($item['name']);
        $ratio = ($food['unit'] === 'pcs') ? $item['quantity'] : ($item['quantity'] / 100);

        $item['calories'] = $food['calories'] * $ratio;
        $item['protein'] = $food['protein'] * $ratio;
        $item['carbs'] = $food['carbs'] * $ratio;
        $item['fats'] = $food['fats'] * $ratio;
    }

    protected function getFoodByName(string $name): array
    {
        $pool = $this->dietType === 'veg' ? FoodPool::getVegFoods() : FoodPool::getNonVegFoods();
        foreach ($pool as $food) {
            if ($food['name'] === $name) return $food;
        }

        if ($this->dietType === 'nonveg') {
            foreach (FoodPool::getVegFoods() as $food) {
                if ($food['name'] === $name) return $food;
            }
        }

        throw new \Exception("Food not found: {$name}");
    }

    protected function createMealItem(string $name, float $baseQuantity): array
    {
        $food = $this->getFoodByName($name);
        $ratio = ($food['unit'] === 'pcs') ? $baseQuantity : ($baseQuantity / 100);

        return [
            'name' => $food['name'],
            'unit' => $food['unit'],
            'base_quantity' => $baseQuantity,
            'base_calories' => $food['calories'] * $ratio,
            'base_protein' => $food['protein'] * $ratio,
            'base_carbs' => $food['carbs'] * $ratio,
            'base_fats' => $food['fats'] * $ratio,
        ];
    }
}
