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
        return [
            // Template 1
            [
                'breakfast' => [
                    $this->createMealItem('Greek Yogurt', 150),
                    $this->createMealItem('Apple', 1),
                    $this->createMealItem('Chia Seeds', 10),
                ],
                'lunch' => [
                    $this->createMealItem($this->dietType === 'veg' ? 'Tofu' : 'Tuna (Canned)', 100),
                    $this->createMealItem('Broccoli', 150),
                    $this->createMealItem('Spinach', 100),
                ],
                'snack' => [
                    $this->createMealItem('Almonds', 15),
                ],
                'dinner' => [
                    $this->createMealItem($this->dietType === 'veg' ? 'Kidney Beans' : 'Tilapia', 100),
                    $this->createMealItem('Green Peas', 50),
                    $this->createMealItem('Mushrooms', 100),
                ]
            ],
            // Template 2
            [
                'breakfast' => [
                    $this->createMealItem($this->dietType === 'veg' ? 'Soy Milk' : 'Egg White', $this->dietType === 'veg' ? 200 : 4),
                    $this->createMealItem('Banana', 1),
                ],
                'lunch' => [
                    $this->createMealItem($this->dietType === 'veg' ? 'Hummus' : 'Shrimp', 100),
                    $this->createMealItem('Quinoa', 50),
                    $this->createMealItem('Broccoli', 150),
                ],
                'snack' => [
                    $this->createMealItem('Pumpkin Seeds', 15),
                ],
                'dinner' => [
                    $this->createMealItem($this->dietType === 'veg' ? 'Cottage Cheese' : 'Cod', 100),
                    $this->createMealItem('Sweet Potato', 50),
                    $this->createMealItem('Spinach', 150),
                ]
            ]
        ];
    }
}
