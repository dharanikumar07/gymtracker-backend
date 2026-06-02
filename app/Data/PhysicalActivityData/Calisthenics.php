<?php

namespace App\Data\PhysicalActivityData;

class Calisthenics extends AbstractPhysicalActivity
{
    public function getData(): array
    {
        return [
            'units' => $this->getAvailableUnitTypes(),

            'metrics_types' => $this->getAvailableMetricTypes(),

            'physical_activity_type' => 'calisthenics',

            'calisthenics' => [

                'Mon' => [
                    'workouts' => [
                        [
                            'name' => 'Push Ups',
                            'target_muscles' => ['chest', 'shoulders', 'triceps', 'core'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 4,
                                    'reps' => 12,
                                    'rest' => 60
                                ]
                            ]
                        ],
                        [
                            'name' => 'Dips',
                            'target_muscles' => ['triceps', 'chest', 'shoulders'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 10,
                                    'rest' => 60
                                ]
                            ]
                        ],
                        [
                            'name' => 'Pike Push Ups',
                            'target_muscles' => ['shoulders', 'triceps', 'upper_chest', 'core'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 10,
                                    'rest' => 60
                                ]
                            ]
                        ],
                    ]
                ],

                'Tue' => [
                    'workouts' => [
                        [
                            'name' => 'Pull Ups',
                            'target_muscles' => ['lats', 'back', 'biceps', 'forearms'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 4,
                                    'reps' => 8,
                                    'rest' => 90
                                ]
                            ]
                        ],
                        [
                            'name' => 'Chin Ups',
                            'target_muscles' => ['biceps', 'lats', 'back', 'forearms'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 8,
                                    'rest' => 90
                                ]
                            ]
                        ],
                        [
                            'name' => 'Inverted Row',
                            'target_muscles' => ['back', 'lats', 'rear_delts', 'biceps', 'core'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 10,
                                    'rest' => 60
                                ]
                            ]
                        ],
                    ]
                ],

                'Wed' => [
                    'workouts' => [
                        [
                            'name' => 'Bodyweight Squats',
                            'target_muscles' => ['quads', 'glutes', 'hamstrings', 'core'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 4,
                                    'reps' => 15,
                                    'rest' => 60
                                ]
                            ]
                        ],
                        [
                            'name' => 'Walking Lunges',
                            'target_muscles' => ['quads', 'glutes', 'hamstrings', 'calves', 'core'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 12,
                                    'rest' => 60
                                ]
                            ]
                        ],
                        [
                            'name' => 'Glute Bridge',
                            'target_muscles' => ['glutes', 'hamstrings', 'core', 'lower_back'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 15,
                                    'rest' => 60
                                ]
                            ]
                        ],
                    ]
                ],

                'Thu' => [
                    'workouts' => [
                        [
                            'name' => 'Plank',
                            'target_muscles' => ['core', 'abs', 'shoulders', 'lower_back'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 45,
                                    'duration_unit' => 'seconds',
                                    'rest' => 30
                                ]
                            ]
                        ],
                        [
                            'name' => 'Hanging Knee Raises',
                            'target_muscles' => ['abs', 'core', 'hip_flexors', 'forearms'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 12,
                                    'rest' => 45
                                ]
                            ]
                        ],
                        [
                            'name' => 'Mountain Climbers',
                            'target_muscles' => ['core', 'abs', 'shoulders', 'quads', 'cardio'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 40,
                                    'duration_unit' => 'seconds',
                                    'rest' => 30
                                ]
                            ]
                        ],
                    ]
                ],

                'Fri' => [
                    'workouts' => [
                        [
                            'name' => 'Decline Push Ups',
                            'target_muscles' => ['upper_chest', 'shoulders', 'triceps', 'core'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 4,
                                    'reps' => 10,
                                    'rest' => 60
                                ]
                            ]
                        ],
                        [
                            'name' => 'Bench Dips',
                            'target_muscles' => ['triceps', 'chest', 'shoulders'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 12,
                                    'rest' => 60
                                ]
                            ]
                        ],
                        [
                            'name' => 'Handstand Hold',
                            'target_muscles' => ['shoulders', 'triceps', 'core', 'upper_back', 'forearms'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 30,
                                    'duration_unit' => 'seconds',
                                    'rest' => 45
                                ]
                            ]
                        ],
                    ]
                ],

                'Sat' => [
                    'workouts' => [
                        [
                            'name' => 'Wide Grip Pull Ups',
                            'target_muscles' => ['lats', 'back', 'biceps', 'forearms'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 4,
                                    'reps' => 6,
                                    'rest' => 90
                                ]
                            ]
                        ],
                        [
                            'name' => 'Australian Pull Ups',
                            'target_muscles' => ['back', 'lats', 'rear_delts', 'biceps', 'core'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 12,
                                    'rest' => 60
                                ]
                            ]
                        ],
                        [
                            'name' => 'Chin Up Hold',
                            'target_muscles' => ['biceps', 'lats', 'back', 'forearms', 'core'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 20,
                                    'duration_unit' => 'seconds',
                                    'rest' => 45
                                ]
                            ]
                        ],
                    ]
                ],

                'Sun' => [
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
                ]

            ]
        ];
    }
}
