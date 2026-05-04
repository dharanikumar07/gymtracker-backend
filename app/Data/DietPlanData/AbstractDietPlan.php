<?php

namespace App\Data\DietPlanData;

abstract class AbstractDietPlan implements DietPlanInterface
{
    protected string $dietType;

    public function __construct(string $dietType)
    {
        $this->dietType = str_replace('_', '', strtolower($dietType));
    }

    abstract protected function getCalorieAdjustment(): int;
    abstract protected function getMealTemplates(): array;

    public function generate(float $weight): array
    {
        $maintenanceCalories = $weight * 2.2 * 14;
        $targetCalories = $maintenanceCalories + $this->getCalorieAdjustment();

        if ($targetCalories < 1200) $targetCalories = 1200;

        $proteinGrams = $weight * 2;
        $proteinCalories = $proteinGrams * 4;
        $remainingCalories = $targetCalories - $proteinCalories;

        $carbsGrams = ($remainingCalories * 0.60) / 4;
        $fatsGrams = ($remainingCalories * 0.40) / 9;

        $targets = [
            'calories' => (int) round($targetCalories),
            'protein' => (int) round($proteinGrams),
            'carbs' => (int) round($carbsGrams),
            'fats' => (int) round($fatsGrams),
        ];

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

    protected function balanceDay(array $template, array $targets): array
    {
        $balancedMeals = [];

        foreach ($template as $mealKey => $meal) {
            $balancedMeal = $meal;
            $balancedMeal['food_data'] = $this->scaleMealFoods($meal['foods'], $meal['target_calories']);

            $mealTotals = $this->sumMealTotals($balancedMeal['food_data']);
            $balancedMeal['calories'] = $mealTotals['calories'];
            $balancedMeal['protein'] = $mealTotals['protein'];
            $balancedMeal['carbs'] = $mealTotals['carbs'];
            $balancedMeal['fats'] = $mealTotals['fats'];

            unset($balancedMeal['foods']);
            unset($balancedMeal['target_calories']);

            $balancedMeals[$mealKey] = $balancedMeal;
        }

        return $balancedMeals;
    }

    protected function scaleMealFoods(array $foods, float $targetCalories): array
    {
        $currentCals = 0;
        foreach ($foods as $food) {
            $item = $this->getFoodByName($food['name']);
            $ratio = ($item['unit'] === 'pcs') ? $food['quantity'] : ($food['quantity'] / 100);
            $currentCals += $item['calories'] * $ratio;
        }

        $scaleFactor = $currentCals > 0 ? ($targetCalories / $currentCals) : 1;
        $scaledFoods = [];

        foreach ($foods as $food) {
            $item = $this->getFoodByName($food['name']);
            $scaledQty = $food['quantity'] * $scaleFactor;
            $ratio = ($item['unit'] === 'pcs') ? $scaledQty : ($scaledQty / 100);

            $scaledFoods[] = [
                'name' => $food['name'],
                'quantity' => round($scaledQty, 2),
                'unit' => $item['unit'],
                'calories' => (int) round($item['calories'] * $ratio),
                'protein' => round($item['protein'] * $ratio, 1),
                'carbs' => round($item['carbs'] * $ratio, 1),
                'fats' => round($item['fats'] * $ratio, 1),
            ];
        }

        return $scaledFoods;
    }

    protected function sumMealTotals(array $foodData): array
    {
        $totals = ['calories' => 0, 'protein' => 0, 'carbs' => 0, 'fats' => 0];

        foreach ($foodData as $food) {
            $totals['calories'] += $food['calories'];
            $totals['protein'] += $food['protein'];
            $totals['carbs'] += $food['carbs'];
            $totals['fats'] += $food['fats'];
        }

        return [
            'calories' => (int) round($totals['calories']),
            'protein' => (int) round($totals['protein']),
            'carbs' => (int) round($totals['carbs']),
            'fats' => (int) round($totals['fats']),
        ];
    }

    protected function getFoodByName(string $name): array
    {
        $isNonVeg = in_array($this->dietType, ['nonveg', 'non_veg']);
        $pool = $isNonVeg ? FoodLibrary::getNonVegFoods() : FoodLibrary::getVegFoods();
        foreach ($pool as $food) {
            if ($food['name'] === $name) return $food;
        }

        if ($isNonVeg) {
            foreach (FoodLibrary::getVegFoods() as $food) {
                if ($food['name'] === $name) return $food;
            }
        }

        throw new \Exception("Food not found: {$name}");
    }

    protected function createMealFood(string $name, float $quantity): array
    {
        return [
            'name' => $name,
            'quantity' => $quantity,
        ];
    }
}
