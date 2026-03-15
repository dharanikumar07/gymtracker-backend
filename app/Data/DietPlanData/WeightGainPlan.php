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
        return [
            // Template 1
            [
                'breakfast' => [
                    $this->createMealItem('Oats', 100),
                    $this->createMealItem('Milk', 250),
                    $this->createMealItem('Banana', 1),
                ],
                'lunch' => [
                    $this->createMealItem($this->dietType === 'veg' ? 'Paneer' : 'Chicken Breast', 150),
                    $this->createMealItem('Brown Rice', 200),
                    $this->createMealItem('Broccoli', 100),
                ],
                'snack' => [
                    $this->createMealItem('Almonds', 30),
                    $this->createMealItem('Apple', 1),
                ],
                'dinner' => [
                    $this->createMealItem($this->dietType === 'veg' ? 'Chickpeas' : 'Salmon', 150),
                    $this->createMealItem('Sweet Potato', 200),
                    $this->createMealItem('Avocado', 50),
                ]
            ],
            // Template 2
            [
                'breakfast' => [
                    $this->createMealItem($this->dietType === 'veg' ? 'Greek Yogurt' : 'Egg (Whole)', $this->dietType === 'veg' ? 200 : 3),
                    $this->createMealItem('Whole Wheat Bread', 2),
                    $this->createMealItem('Peanut Butter', 30),
                ],
                'lunch' => [
                    $this->createMealItem($this->dietType === 'veg' ? 'Soy Chunks' : 'Lean Beef', 150),
                    $this->createMealItem('Quinoa', 200),
                    $this->createMealItem('Spinach', 100),
                ],
                'snack' => [
                    $this->createMealItem('Walnuts', 30),
                    $this->createMealItem('Greek Yogurt', 150),
                ],
                'dinner' => [
                    $this->createMealItem($this->dietType === 'veg' ? 'Tofu' : 'Turkey Breast', 150),
                    $this->createMealItem('Potatoes (Boiled)', 200),
                    $this->createMealItem('Mushrooms', 100),
                ]
            ]
        ];
    }
}
