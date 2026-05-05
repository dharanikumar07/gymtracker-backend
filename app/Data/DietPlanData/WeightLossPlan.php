<?php

namespace App\Data\DietPlanData;

class WeightLossPlan extends AbstractDietPlan
{
    protected function getCalorieAdjustment(): int
    {
        return -500;
    }

    protected function getMealTemplates(): array
    {
        $lunch = $this->dietType === 'veg' ? 'Tofu' : 'Tuna (Canned)';
        $dinner = $this->dietType === 'veg' ? 'Kidney Beans' : 'Tilapia';
        $breakfast = $this->dietType === 'veg' ? 'Soy Milk' : 'Egg White';
        $breakfastQty = $this->dietType === 'veg' ? 200 : 4;
        $lunch2 = $this->dietType === 'veg' ? 'Hummus' : 'Shrimp';
        $dinner2 = $this->dietType === 'veg' ? 'Cottage Cheese' : 'Cod';

        return [
            [
                'breakfast' => [
                    'meal_name' => 'Light Yogurt Bowl',
                    'time_period' => 'breakfast',
                    'target_calories' => 300,
                    'foods' => [
                        $this->createMealFood('Greek Yogurt', 150),
                        $this->createMealFood('Apple', 1),
                        $this->createMealFood('Chia Seeds', 10),
                    ],
                ],
                'lunch' => [
                    'meal_name' => $this->dietType === 'veg' ? 'Tofu Veggie Plate' : 'Tuna Green Plate',
                    'time_period' => 'lunch',
                    'target_calories' => 400,
                    'foods' => [
                        $this->createMealFood($lunch, 100),
                        $this->createMealFood('Broccoli', 150),
                        $this->createMealFood('Spinach', 100),
                    ],
                ],
                'snack' => [
                    'meal_name' => 'Almond Crunch',
                    'time_period' => 'snack',
                    'target_calories' => 120,
                    'foods' => [
                        $this->createMealFood('Almonds', 15),
                    ],
                ],
                'dinner' => [
                    'meal_name' => $this->dietType === 'veg' ? 'Bean Mushroom Bowl' : 'Tilapia Green Bowl',
                    'time_period' => 'dinner',
                    'target_calories' => 350,
                    'foods' => [
                        $this->createMealFood($dinner, 100),
                        $this->createMealFood('Green Peas', 50),
                        $this->createMealFood('Mushrooms', 100),
                    ],
                ]
            ],
            [
                'breakfast' => [
                    'meal_name' => $this->dietType === 'veg' ? 'Soy Banana Smoothie' : 'Egg White Banana',
                    'time_period' => 'breakfast',
                    'target_calories' => 250,
                    'foods' => [
                        $this->createMealFood($breakfast, $breakfastQty),
                        $this->createMealFood('Banana', 1),
                    ],
                ],
                'lunch' => [
                    'meal_name' => $this->dietType === 'veg' ? 'Hummus Quinoa Bowl' : 'Shrimp Quinoa Plate',
                    'time_period' => 'lunch',
                    'target_calories' => 400,
                    'foods' => [
                        $this->createMealFood($lunch2, 100),
                        $this->createMealFood('Quinoa', 50),
                        $this->createMealFood('Broccoli', 150),
                    ],
                ],
                'snack' => [
                    'meal_name' => 'Seed Mix',
                    'time_period' => 'snack',
                    'target_calories' => 120,
                    'foods' => [
                        $this->createMealFood('Pumpkin Seeds', 15),
                    ],
                ],
                'dinner' => [
                    'meal_name' => $this->dietType === 'veg' ? 'Cottage Veggie Plate' : 'Cod Sweet Plate',
                    'time_period' => 'dinner',
                    'target_calories' => 350,
                    'foods' => [
                        $this->createMealFood($dinner2, 100),
                        $this->createMealFood('Sweet Potato', 50),
                        $this->createMealFood('Spinach', 150),
                    ],
                ]
            ]
        ];
    }
}
