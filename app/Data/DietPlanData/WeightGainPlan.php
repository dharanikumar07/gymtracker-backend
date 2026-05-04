<?php

namespace App\Data\DietPlanData;

class WeightGainPlan extends AbstractDietPlan
{
    protected function getCalorieAdjustment(): int
    {
        return 500;
    }

    protected function getMealTemplates(): array
    {
        $protein = $this->dietType === 'veg' ? 'Paneer' : 'Chicken Breast';
        $protein2 = $this->dietType === 'veg' ? 'Chickpeas' : 'Salmon';
        $breakfast = $this->dietType === 'veg' ? 'Greek Yogurt' : 'Egg (Whole)';
        $breakfastQty = $this->dietType === 'veg' ? 200 : 3;
        $lunch = $this->dietType === 'veg' ? 'Soy Chunks' : 'Lean Beef';
        $dinner = $this->dietType === 'veg' ? 'Tofu' : 'Turkey Breast';

        return [
            [
                'breakfast' => [
                    'meal_name' => 'Power Oats Bowl',
                    'time_period' => 'breakfast',
                    'target_calories' => 600,
                    'foods' => [
                        $this->createMealFood('Oats', 100),
                        $this->createMealFood('Milk', 250),
                        $this->createMealFood('Banana', 1),
                    ],
                ],
                'lunch' => [
                    'meal_name' => $this->dietType === 'veg' ? 'Paneer Rice Plate' : 'Chicken Rice Bowl',
                    'time_period' => 'lunch',
                    'target_calories' => 800,
                    'foods' => [
                        $this->createMealFood($protein, 150),
                        $this->createMealFood('Brown Rice', 200),
                        $this->createMealFood('Broccoli', 100),
                    ],
                ],
                'snack' => [
                    'meal_name' => 'Nutty Fruit Snack',
                    'time_period' => 'snack',
                    'target_calories' => 350,
                    'foods' => [
                        $this->createMealFood('Almonds', 30),
                        $this->createMealFood('Apple', 1),
                    ],
                ],
                'dinner' => [
                    'meal_name' => $this->dietType === 'veg' ? 'Chickpea Potato Bowl' : 'Salmon Sweet Potato Plate',
                    'time_period' => 'dinner',
                    'target_calories' => 750,
                    'foods' => [
                        $this->createMealFood($protein2, 150),
                        $this->createMealFood('Sweet Potato', 200),
                        $this->createMealFood('Avocado', 50),
                    ],
                ]
            ],
            [
                'breakfast' => [
                    'meal_name' => 'Protein Breakfast Plate',
                    'time_period' => 'breakfast',
                    'target_calories' => 650,
                    'foods' => [
                        $this->createMealFood($breakfast, $breakfastQty),
                        $this->createMealFood('Whole Wheat Bread', 2),
                        $this->createMealFood('Peanut Butter', 30),
                    ],
                ],
                'lunch' => [
                    'meal_name' => $this->dietType === 'veg' ? 'Soy Quinoa Bowl' : 'Beef Quinoa Plate',
                    'time_period' => 'lunch',
                    'target_calories' => 800,
                    'foods' => [
                        $this->createMealFood($lunch, 150),
                        $this->createMealFood('Quinoa', 200),
                        $this->createMealFood('Spinach', 100),
                    ],
                ],
                'snack' => [
                    'meal_name' => 'Yogurt Walnut Mix',
                    'time_period' => 'snack',
                    'target_calories' => 350,
                    'foods' => [
                        $this->createMealFood('Walnuts', 30),
                        $this->createMealFood('Greek Yogurt', 150),
                    ],
                ],
                'dinner' => [
                    'meal_name' => $this->dietType === 'veg' ? 'Tofu Mushroom Bowl' : 'Turkey Mushroom Plate',
                    'time_period' => 'dinner',
                    'target_calories' => 700,
                    'foods' => [
                        $this->createMealFood($dinner, 150),
                        $this->createMealFood('Potatoes (Boiled)', 200),
                        $this->createMealFood('Mushrooms', 100),
                    ],
                ]
            ]
        ];
    }
}
