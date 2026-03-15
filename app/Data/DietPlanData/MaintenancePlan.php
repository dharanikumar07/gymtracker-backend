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
        return [
            // Template 1
            [
                'breakfast' => [
                    $this->createMealItem('Oats', 80),
                    $this->createMealItem('Milk', 200),
                    $this->createMealItem('Banana', 1),
                ],
                'lunch' => [
                    $this->createMealItem($this->dietType === 'veg' ? 'Paneer' : 'Chicken Breast', 120),
                    $this->createMealItem('Brown Rice', 150),
                    $this->createMealItem('Broccoli', 100),
                ],
                'snack' => [
                    $this->createMealItem('Almonds', 20),
                ],
                'dinner' => [
                    $this->createMealItem($this->dietType === 'veg' ? 'Chickpeas' : 'Salmon', 120),
                    $this->createMealItem('Sweet Potato', 150),
                    $this->createMealItem('Spinach', 100),
                ]
            ],
            // Template 2
            [
                'breakfast' => [
                    $this->createMealItem($this->dietType === 'veg' ? 'Cottage Cheese' : 'Egg (Whole)', $this->dietType === 'veg' ? 150 : 2),
                    $this->createMealItem('Whole Wheat Bread', 2),
                ],
                'lunch' => [
                    $this->createMealItem($this->dietType === 'veg' ? 'Soy Chunks' : 'Turkey Breast', 120),
                    $this->createMealItem('Quinoa', 150),
                    $this->createMealItem('Mushrooms', 100),
                ],
                'snack' => [
                    $this->createMealItem('Apple', 1),
                ],
                'dinner' => [
                    $this->createMealItem($this->dietType === 'veg' ? 'Lentils (Cooked)' : 'Tilapia', 120),
                    $this->createMealItem('Potatoes (Boiled)', 150),
                    $this->createMealItem('Broccoli', 100),
                ]
            ]
        ];
    }
}
