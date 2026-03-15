# 🥗 GymOS: Diet Plan Module Documentation

This document outlines the architecture, logic, and algorithms used in the GymOS Diet Plan Module.

---

## 🚀 Overview
The Diet Plan Module is an industry-level nutrition engine designed to provide personalized, macro-balanced dietary protocols. Unlike basic trackers, it uses a **Proportional Template Scaling (PTS)** algorithm to dynamically adjust food quantities based on a user's specific body weight and fitness goals.

## 🧠 Core Logic & Formulas

### 1. Daily Calorie Target
The system first establishes a baseline using a simplified Total Daily Energy Expenditure (TDEE) formula:
*   **Maintenance Calories:** `Current Weight (kg) * 2.2 * 14`
*   **Goal Adjustments:**
    *   **Weight Gain:** `Maintenance + 500 kcal`
    *   **Weight Loss:** `Maintenance - 500 kcal` (Min. Floor: 1200 kcal)
    *   **Maintenance:** No change.

### 2. Macro-Nutrient Split
Once the calorie target is set, the system calculates macros:
*   **Protein:** `2.0g per kg of body weight`
*   **Fats:** `40% of remaining calories` after protein.
*   **Carbs:** `60% of remaining calories` after protein.

---

## ⚖️ The Algorithm: Proportional Template Scaling (PTS)

The "Secret Sauce" of this module is how it handles different weights (e.g., 55kg vs 60kg) without needing a manual setup for every gram.

### How it works:
1.  **Base Templates:** We define "Master Templates" designed for a standard calorie count (e.g., 2000 kcal).
2.  **Calculate Scale Factor:** The system finds the ratio between the User's Target and the Template's Base:
    *   `Scale Factor = User Target Calories / Template Base Calories`
3.  **Dynamic Portioning:** Every food item's quantity (grams/units) is multiplied by this **Scale Factor**.

### Example:
If a **Mass Gainer Breakfast** template has **100g of Oats** for a 2000 kcal base:
*   **User A (55kg - Needs 2194 kcal):** Gets `100g * 1.097 = 109.7g` of Oats.
*   **User B (60kg - Needs 2348 kcal):** Gets `100g * 1.174 = 117.4g` of Oats.

---

## 🍱 Food Database (The Food Pool)
The system maintains a repository of **60 high-quality food items** categorized by dietary preference:

*   **30 Vegetarian Items:** High-protein plant sources like Paneer, Tofu, Soy Chunks, Lentils, and Greek Yogurt.
*   **30 Non-Vegetarian Items:** Lean meats and seafood like Chicken Breast, Salmon, Eggs, Turkey, and Lean Beef.

**Selection Logic:**
*   If a user is **Veg**, the system pulls exclusively from the Veg pool.
*   If **Non-Veg**, it utilizes both pools to ensure maximum variety and nutrient density.

---

## 🛠 Backend Architecture

The module is built with a clean, service-oriented architecture in `app/Data/DietPlanData`:

| File | Responsibility |
| :--- | :--- |
| `FoodPool.php` | The central repository of 60 foods with calories/macros per 100g. |
| `DietPlanFactory.php` | Orchestrator that chooses the right plan based on user goals. |
| `AbstractDietPlan.php` | Contains the core PTS scaling and calorie calculation logic. |
| `WeightGainPlan.php` | High-calorie templates focused on mass building. |
| `WeightLossPlan.php` | Low-calorie, high-volume templates focused on satiety. |
| `MaintenancePlan.php` | Balanced templates for consistent energy levels. |

---

## 🔄 7-Day Auto-Generation & Rotation
To prevent "Diet Fatigue," the system doesn't just repeat the same day:
*   **Multiple Templates:** Each plan contains multiple meal patterns.
*   **Modulo Rotation:** The system rotates these templates across the 7 days of the week (`Mon-Sun`).
*   **Persistence:** Once generated, the plan is saved into the `diet_plan_items` table, becoming the user's "Fixed Protocol" until they choose to recalculate.

---

## 📈 Tracking & Execution
The system integrates with `DietController.php` to:
1.  **Auto-Generate:** Creates the first plan instantly based on onboarding data.
2.  **Track Progress:** Allows users to log their "Actual Intake" against the "Prescribed Plan."
3.  **Analytics:** Provides a daily dashboard view of **Target vs. Actual** macro consumption.
