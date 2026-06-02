<?php

namespace App\Data\PhysicalActivityData;

class StrengthTraining extends AbstractPhysicalActivity
{
    public function getData(): array
    {
        return [

            'units' => $this->getAvailableUnitTypes(),

            'metrics_types' => $this->getAvailableMetricTypes(),

            'physical_activity_type' => 'strength_training',

            'strength_training' => [
                'Mon' => [
                    'workouts' => [
                        [
                            'name' => 'Barbell Bench Press',
                            'target_muscles' => ['chest', 'triceps', 'shoulders'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 4, 'reps' => 8, 'rest' => 90]
                            ]
                        ],
                        [
                            'name' => 'Incline Dumbbell Press',
                            'target_muscles' => ['upper_chest', 'shoulders', 'triceps'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Cable Chest Fly',
                            'target_muscles' => ['chest'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Barbell Curl',
                            'target_muscles' => ['biceps', 'forearms'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Hammer Curl',
                            'target_muscles' => ['biceps', 'brachialis', 'forearms'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Push Ups',
                            'target_muscles' => ['chest', 'triceps', 'shoulders', 'core'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 15, 'rest' => 60]
                            ]
                        ],
                    ]
                ],

                'Tue' => [
                    'workouts' => [
                        [
                            'name' => 'Pull Ups',
                            'target_muscles' => ['back', 'lats', 'biceps', 'forearms'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 4, 'reps' => 8, 'rest' => 90]
                            ]
                        ],
                        [
                            'name' => 'Bent Over Row',
                            'target_muscles' => ['back', 'lats', 'traps', 'rear_delts', 'biceps'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Tricep Pushdown',
                            'target_muscles' => ['triceps'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Close Grip Bench Press',
                            'target_muscles' => ['triceps', 'chest', 'shoulders'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Hanging Leg Raise',
                            'target_muscles' => ['abs', 'core', 'hip_flexors'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => ['sets' => 3, 'duration' => 30, 'duration_unit' => 'seconds', 'rest' => 45]
                            ]
                        ],
                        [
                            'name' => 'Plank',
                            'target_muscles' => ['core', 'abs', 'shoulders', 'lower_back'],
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => ['duration' => 60, 'duration_unit' => 'seconds']
                            ]
                        ],
                    ]
                ],

                'Wed' => [
                    'workouts' => [
                        [
                            'name' => 'Barbell Squat',
                            'target_muscles' => ['quads', 'glutes', 'hamstrings', 'core'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 4, 'reps' => 8, 'rest' => 120]
                            ]
                        ],
                        [
                            'name' => 'Leg Press',
                            'target_muscles' => ['quads', 'glutes', 'hamstrings'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 90]
                            ]
                        ],
                        [
                            'name' => 'Walking Lunges',
                            'target_muscles' => ['quads', 'glutes', 'hamstrings', 'calves'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Shoulder Press',
                            'target_muscles' => ['shoulders', 'triceps', 'upper_chest'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Lateral Raises',
                            'target_muscles' => ['shoulders', 'side_delts'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 45]
                            ]
                        ],
                        [
                            'name' => 'Burpees',
                            'target_muscles' => ['full_body', 'chest', 'shoulders', 'core', 'quads', 'glutes'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => ['sets' => 3, 'duration' => 30, 'duration_unit' => 'seconds', 'rest' => 45]
                            ]
                        ],
                    ]
                ],

                'Thu' => [
                    'workouts' => [
                        [
                            'name' => 'Rest Day',
                            'target_muscles' => ['recovery'],
                            'metrics' => [
                                'type' => 'rest',
                                'data' => []
                            ]
                        ]
                    ]
                ],

                'Fri' => [
                    'workouts' => [
                        [
                            'name' => 'Decline Bench Press',
                            'target_muscles' => ['lower_chest', 'triceps', 'shoulders'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 4, 'reps' => 8, 'rest' => 90]
                            ]
                        ],
                        [
                            'name' => 'Incline Push Ups',
                            'target_muscles' => ['chest', 'shoulders', 'triceps', 'core'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Pec Deck Machine',
                            'target_muscles' => ['chest'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Preacher Curl',
                            'target_muscles' => ['biceps'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Concentration Curl',
                            'target_muscles' => ['biceps'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Diamond Push Ups',
                            'target_muscles' => ['triceps', 'chest', 'shoulders', 'core'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 15, 'rest' => 60]
                            ]
                        ],
                    ]
                ],

                'Sat' => [
                    'workouts' => [
                        [
                            'name' => 'Lat Pulldown',
                            'target_muscles' => ['lats', 'back', 'biceps', 'rear_delts'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 4, 'reps' => 10, 'rest' => 90]
                            ]
                        ],
                        [
                            'name' => 'T-Bar Row',
                            'target_muscles' => ['back', 'lats', 'traps', 'rear_delts', 'biceps'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Dips',
                            'target_muscles' => ['triceps', 'chest', 'shoulders'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Skull Crushers',
                            'target_muscles' => ['triceps'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Russian Twist',
                            'target_muscles' => ['obliques', 'abs', 'core'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => ['sets' => 3, 'duration' => 30, 'duration_unit' => 'seconds', 'rest' => 45]
                            ]
                        ],
                        [
                            'name' => 'Bicycle Crunch',
                            'target_muscles' => ['abs', 'obliques', 'core', 'hip_flexors'],
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => ['duration' => 60, 'duration_unit' => 'seconds']
                            ]
                        ],
                    ]
                ],

                'Sun' => [
                    'workouts' => [
                        [
                            'name' => 'Front Squat',
                            'target_muscles' => ['quads', 'glutes', 'core', 'upper_back'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 4, 'reps' => 8, 'rest' => 120]
                            ]
                        ],
                        [
                            'name' => 'Bulgarian Split Squat',
                            'target_muscles' => ['quads', 'glutes', 'hamstrings', 'calves'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 90]
                            ]
                        ],
                        [
                            'name' => 'Leg Extension',
                            'target_muscles' => ['quads'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Arnold Press',
                            'target_muscles' => ['shoulders', 'front_delts', 'side_delts', 'triceps'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 60]
                            ]
                        ],
                        [
                            'name' => 'Rear Delt Fly',
                            'target_muscles' => ['rear_delts', 'upper_back', 'traps'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 45]
                            ]
                        ],
                        [
                            'name' => 'Jump Squats',
                            'target_muscles' => ['quads', 'glutes', 'hamstrings', 'calves', 'core'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => ['sets' => 3, 'duration' => 30, 'duration_unit' => 'seconds', 'rest' => 45]
                            ]
                        ],
                    ]
                ]

            ]

        ];
    }
}
