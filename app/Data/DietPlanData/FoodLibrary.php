<?php

namespace App\Data\DietPlanData;

class FoodLibrary
{
    public static function getVegFoods(): array
    {
        return [
            ['name' => 'Oats', 'unit' => 'g', 'calories' => 389, 'protein' => 17, 'carbs' => 66, 'fats' => 7, 'quantity' => 100],
            ['name' => 'Milk', 'unit' => 'ml', 'calories' => 60, 'protein' => 3, 'carbs' => 5, 'fats' => 3, 'quantity' => 100],
            ['name' => 'Paneer', 'unit' => 'g', 'calories' => 265, 'protein' => 18, 'carbs' => 6, 'fats' => 20, 'quantity' => 100],
            ['name' => 'Tofu', 'unit' => 'g', 'calories' => 76, 'protein' => 8, 'carbs' => 2, 'fats' => 4.8, 'quantity' => 100],
            ['name' => 'Lentils (Cooked)', 'unit' => 'g', 'calories' => 116, 'protein' => 9, 'carbs' => 20, 'fats' => 0.4, 'quantity' => 100],
            ['name' => 'Chickpeas', 'unit' => 'g', 'calories' => 164, 'protein' => 8.9, 'carbs' => 27, 'fats' => 2.6, 'quantity' => 100],
            ['name' => 'Brown Rice', 'unit' => 'g', 'calories' => 111, 'protein' => 2.6, 'carbs' => 23, 'fats' => 0.9, 'quantity' => 100],
            ['name' => 'Sweet Potato', 'unit' => 'g', 'calories' => 86, 'protein' => 1.6, 'carbs' => 20, 'fats' => 0.1, 'quantity' => 100],
            ['name' => 'Quinoa', 'unit' => 'g', 'calories' => 120, 'protein' => 4.4, 'carbs' => 21, 'fats' => 1.9, 'quantity' => 100],
            ['name' => 'Almonds', 'unit' => 'g', 'calories' => 579, 'protein' => 21, 'carbs' => 22, 'fats' => 50, 'quantity' => 100],
            ['name' => 'Walnuts', 'unit' => 'g', 'calories' => 654, 'protein' => 15, 'carbs' => 14, 'fats' => 65, 'quantity' => 100],
            ['name' => 'Peanut Butter', 'unit' => 'g', 'calories' => 588, 'protein' => 25, 'carbs' => 20, 'fats' => 50, 'quantity' => 100],
            ['name' => 'Greek Yogurt', 'unit' => 'g', 'calories' => 59, 'protein' => 10, 'carbs' => 3.6, 'fats' => 0.4, 'quantity' => 100],
            ['name' => 'Banana', 'unit' => 'pcs', 'calories' => 89, 'protein' => 1.1, 'carbs' => 23, 'fats' => 0.3, 'quantity' => 1],
            ['name' => 'Apple', 'unit' => 'pcs', 'calories' => 52, 'protein' => 0.3, 'carbs' => 14, 'fats' => 0.2, 'quantity' => 1],
            ['name' => 'Broccoli', 'unit' => 'g', 'calories' => 34, 'protein' => 2.8, 'carbs' => 7, 'fats' => 0.4, 'quantity' => 100],
            ['name' => 'Spinach', 'unit' => 'g', 'calories' => 23, 'protein' => 2.9, 'carbs' => 3.6, 'fats' => 0.4, 'quantity' => 100],
            ['name' => 'Avocado', 'unit' => 'g', 'calories' => 160, 'protein' => 2, 'carbs' => 9, 'fats' => 15, 'quantity' => 100],
            ['name' => 'Chia Seeds', 'unit' => 'g', 'calories' => 486, 'protein' => 17, 'carbs' => 42, 'fats' => 31, 'quantity' => 100],
            ['name' => 'Flax Seeds', 'unit' => 'g', 'calories' => 534, 'protein' => 18, 'carbs' => 29, 'fats' => 42, 'quantity' => 100],
            ['name' => 'Kidney Beans', 'unit' => 'g', 'calories' => 127, 'protein' => 8.7, 'carbs' => 22.8, 'fats' => 0.5, 'quantity' => 100],
            ['name' => 'Soy Milk', 'unit' => 'ml', 'calories' => 33, 'protein' => 2.8, 'carbs' => 1.8, 'fats' => 1.8, 'quantity' => 100],
            ['name' => 'Cottage Cheese', 'unit' => 'g', 'calories' => 98, 'protein' => 11, 'carbs' => 3.4, 'fats' => 4.3, 'quantity' => 100],
            ['name' => 'Hummus', 'unit' => 'g', 'calories' => 166, 'protein' => 8, 'carbs' => 14, 'fats' => 10, 'quantity' => 100],
            ['name' => 'Pumpkin Seeds', 'unit' => 'g', 'calories' => 559, 'protein' => 30, 'carbs' => 11, 'fats' => 49, 'quantity' => 100],
            ['name' => 'Green Peas', 'unit' => 'g', 'calories' => 81, 'protein' => 5, 'carbs' => 14, 'fats' => 0.4, 'quantity' => 100],
            ['name' => 'Whole Wheat Bread', 'unit' => 'pcs', 'calories' => 69, 'protein' => 3.6, 'carbs' => 12, 'fats' => 0.9, 'quantity' => 1],
            ['name' => 'Mushrooms', 'unit' => 'g', 'calories' => 22, 'protein' => 3.1, 'carbs' => 3.3, 'fats' => 0.3, 'quantity' => 100],
            ['name' => 'Potatoes (Boiled)', 'unit' => 'g', 'calories' => 77, 'protein' => 2, 'carbs' => 17, 'fats' => 0.1, 'quantity' => 100],
            ['name' => 'Soy Chunks', 'unit' => 'g', 'calories' => 345, 'protein' => 52, 'carbs' => 33, 'fats' => 0.5, 'quantity' => 100],
        ];
    }

    public static function getNonVegFoods(): array
    {
        return [
            ['name' => 'Chicken Breast', 'unit' => 'g', 'calories' => 165, 'protein' => 31, 'carbs' => 0, 'fats' => 3.6, 'quantity' => 100],
            ['name' => 'Egg (Whole)', 'unit' => 'pcs', 'calories' => 78, 'protein' => 6, 'carbs' => 0.6, 'fats' => 5, 'quantity' => 1],
            ['name' => 'Egg White', 'unit' => 'pcs', 'calories' => 17, 'protein' => 3.6, 'carbs' => 0.2, 'fats' => 0.1, 'quantity' => 1],
            ['name' => 'Salmon', 'unit' => 'g', 'calories' => 208, 'protein' => 20, 'carbs' => 0, 'fats' => 13, 'quantity' => 100],
            ['name' => 'Tuna (Canned)', 'unit' => 'g', 'calories' => 132, 'protein' => 28, 'carbs' => 0, 'fats' => 1, 'quantity' => 100],
            ['name' => 'Lean Beef', 'unit' => 'g', 'calories' => 250, 'protein' => 26, 'carbs' => 0, 'fats' => 15, 'quantity' => 100],
            ['name' => 'Turkey Breast', 'unit' => 'g', 'calories' => 135, 'protein' => 30, 'carbs' => 0, 'fats' => 1, 'quantity' => 100],
            ['name' => 'Shrimp', 'unit' => 'g', 'calories' => 99, 'protein' => 24, 'carbs' => 0.2, 'fats' => 0.3, 'quantity' => 100],
            ['name' => 'Tilapia', 'unit' => 'g', 'calories' => 128, 'protein' => 26, 'carbs' => 0, 'fats' => 2.7, 'quantity' => 100],
            ['name' => 'Cod', 'unit' => 'g', 'calories' => 82, 'protein' => 18, 'carbs' => 0, 'fats' => 0.7, 'quantity' => 100],
            ['name' => 'Chicken Thigh', 'unit' => 'g', 'calories' => 209, 'protein' => 26, 'carbs' => 0, 'fats' => 11, 'quantity' => 100],
            ['name' => 'Lamb (Lean)', 'unit' => 'g', 'calories' => 294, 'protein' => 25, 'carbs' => 0, 'fats' => 21, 'quantity' => 100],
            ['name' => 'Pork Tenderloin', 'unit' => 'g', 'calories' => 143, 'protein' => 26, 'carbs' => 0, 'fats' => 3.5, 'quantity' => 100],
            ['name' => 'Whey Protein', 'unit' => 'g', 'calories' => 400, 'protein' => 80, 'carbs' => 5, 'fats' => 5, 'quantity' => 100],
            ['name' => 'Mackerel', 'unit' => 'g', 'calories' => 205, 'protein' => 19, 'carbs' => 0, 'fats' => 14, 'quantity' => 100],
            ['name' => 'Bison', 'unit' => 'g', 'calories' => 143, 'protein' => 21, 'carbs' => 0, 'fats' => 2, 'quantity' => 100],
            ['name' => 'Duck Breast', 'unit' => 'g', 'calories' => 140, 'protein' => 19, 'carbs' => 0, 'fats' => 6, 'quantity' => 100],
            ['name' => 'Sardines', 'unit' => 'g', 'calories' => 208, 'protein' => 25, 'carbs' => 0, 'fats' => 11, 'quantity' => 100],
            ['name' => 'Crab', 'unit' => 'g', 'calories' => 84, 'protein' => 18, 'carbs' => 0, 'fats' => 1.1, 'quantity' => 100],
            ['name' => 'Scallops', 'unit' => 'g', 'calories' => 111, 'protein' => 20, 'carbs' => 3.1, 'fats' => 0.8, 'quantity' => 100],
            ['name' => 'Oats', 'unit' => 'g', 'calories' => 389, 'protein' => 17, 'carbs' => 66, 'fats' => 7, 'quantity' => 100],
            ['name' => 'Brown Rice', 'unit' => 'g', 'calories' => 111, 'protein' => 2.6, 'carbs' => 23, 'fats' => 0.9, 'quantity' => 100],
            ['name' => 'Sweet Potato', 'unit' => 'g', 'calories' => 86, 'protein' => 1.6, 'carbs' => 20, 'fats' => 0.1, 'quantity' => 100],
            ['name' => 'Milk', 'unit' => 'ml', 'calories' => 60, 'protein' => 3, 'carbs' => 5, 'fats' => 3, 'quantity' => 100],
            ['name' => 'Banana', 'unit' => 'pcs', 'calories' => 89, 'protein' => 1.1, 'carbs' => 23, 'fats' => 0.3, 'quantity' => 1],
            ['name' => 'Almonds', 'unit' => 'g', 'calories' => 579, 'protein' => 21, 'carbs' => 22, 'fats' => 50, 'quantity' => 100],
            ['name' => 'Greek Yogurt', 'unit' => 'g', 'calories' => 59, 'protein' => 10, 'carbs' => 3.6, 'fats' => 0.4, 'quantity' => 100],
            ['name' => 'Broccoli', 'unit' => 'g', 'calories' => 34, 'protein' => 2.8, 'carbs' => 7, 'fats' => 0.4, 'quantity' => 100],
            ['name' => 'Avocado', 'unit' => 'g', 'calories' => 160, 'protein' => 2, 'carbs' => 9, 'fats' => 15, 'quantity' => 100],
            ['name' => 'Whole Wheat Bread', 'unit' => 'pcs', 'calories' => 69, 'protein' => 3.6, 'carbs' => 12, 'fats' => 0.9, 'quantity' => 1],
        ];
    }

    public static function getFoodsByType(string $dietType): array
    {
        $dietType = str_replace('_', '', strtolower($dietType));
        $veg = self::getVegFoods();

        if ($dietType === 'veg') {
            return $veg;
        }

        return self::getNonVegFoods();
    }

    public static function getAllFoods(): array
    {
        return [
            'veg' => self::getVegFoods(),
            'nonveg' => self::getNonVegFoods(),
        ];
    }
}
