<?php

namespace App\Data\DietPlanData;

class MaintenancePlan extends AbstractDietPlan
{
    protected function getCalorieAdjustment(): int
    {
        return 0;
    }

    protected function getMealTemplates(): array
    {
        $lunch = $this->dietType === 'veg' ? 'Paneer' : 'Chicken Breast';
        $dinner = $this->dietType === 'veg' ? 'Chickpeas' : 'Salmon';
        $breakfast = $this->dietType === 'veg' ? 'Cottage Cheese' : 'Egg (Whole)';
        $breakfastQty = $this->dietType === 'veg' ? 150 : 2;
        $lunch2 = $this->dietType === 'veg' ? 'Soy Chunks' : 'Turkey Breast';
        $dinner2 = $this->dietType === 'veg' ? 'Lentils (Cooked)' : 'Tilapia';

        return [
            [
                'breakfast' => [
                    'meal_name' => 'Classic Oats Bowl',
                    'time_period' => 'breakfast',
                    'target_calories' => 450,
                    'foods' => [
                        $this->createMealFood('Oats', 80),
                        $this->createMealFood('Milk', 200),
                        $this->createMealFood('Banana', 1),
                    ],
                ],
                'lunch' => [
                    'meal_name' => $this->dietType === 'veg' ? 'Paneer Rice Bowl' : 'Chicken Rice Plate',
                    'time_period' => 'lunch',
                    'target_calories' => 600,
                    'foods' => [
                        $this->createMealFood($lunch, 120),
                        $this->createMealFood('Brown Rice', 150),
                        $this->createMealFood('Broccoli', 100),
                    ],
                ],
                'snack' => [
                    'meal_name' => 'Almond Snack',
                    'time_period' => 'snack',
                    'target_calories' => 150,
                    'foods' => [
                        $this->createMealFood('Almonds', 20),
                    ],
                ],
                'dinner' => [
                    'meal_name' => $this->dietType === 'veg' ? 'Chickpea Sweet Bowl' : 'Salmon Sweet Plate',
                    'time_period' => 'dinner',
                    'target_calories' => 550,
                    'foods' => [
                        $this->createMealFood($dinner, 120),
                        $this->createMealFood('Sweet Potato', 150),
                        $this->createMealFood('Spinach', 100),
                    ],
                ]
            ],
            [
                'breakfast' => [
                    'meal_name' => 'Protein Breakfast',
                    'time_period' => 'breakfast',
                    'target_calories' => 350,
                    'foods' => [
                        $this->createMealFood($breakfast, $breakfastQty),
                        $this->createMealFood('Whole Wheat Bread', 2),
                    ],
                ],
                'lunch' => [
                    'meal_name' => $this->dietType === 'veg' ? 'Soy Quinoa Plate' : 'Turkey Quinoa Bowl',
                    'time_period' => 'lunch',
                    'target_calories' => 600,
                    'foods' => [
                        $this->createMealFood($lunch2, 120),
                        $this->createMealFood('Quinoa', 150),
                        $this->createMealFood('Mushrooms', 100),
                    ],
                ],
                'snack' => [
                    'meal_name' => 'Fresh Apple',
                    'time_period' => 'snack',
                    'target_calories' => 80,
                    'foods' => [
                        $this->createMealFood('Apple', 1),
                    ],
                ],
                'dinner' => [
                    'meal_name' => $this->dietType === 'veg' ? 'Lentil Potato Bowl' : 'Tilapia Veggie Plate',
                    'time_period' => 'dinner',
                    'target_calories' => 500,
                    'foods' => [
                        $this->createMealFood($dinner2, 120),
                        $this->createMealFood('Potatoes (Boiled)', 150),
                        $this->createMealFood('Broccoli', 100),
                    ],
                ]
            ]
        ];
    }
}
