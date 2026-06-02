<?php

namespace App\Data\PhysicalActivityData;

class CardioTraining extends AbstractPhysicalActivity
{
    public function getData(): array
    {
        return [
            'units' => $this->getAvailableUnitTypes(),

            'metrics_types' => $this->getAvailableMetricTypes(),

            'physical_activity_type' => 'cardio_training',

            'cardio_training' => [

                'Mon' => [
                    'workouts' => [
                        [
                            'name' => 'Jump Rope',
                            'target_muscles' => ['cardio', 'calves', 'quads', 'hamstrings', 'shoulders', 'core'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 5,
                                    'duration' => 60,
                                    'duration_unit' => 'seconds',
                                    'rest' => 30
                                ]
                            ]
                        ],
                        [
                            'name' => 'High Knees',
                            'target_muscles' => ['cardio', 'hip_flexors', 'quads', 'calves', 'core'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 4,
                                    'duration' => 45,
                                    'duration_unit' => 'seconds',
                                    'rest' => 30
                                ]
                            ]
                        ],
                        [
                            'name' => 'Jumping Jacks',
                            'target_muscles' => ['cardio', 'full_body', 'shoulders', 'calves', 'glutes', 'core'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 4,
                                    'duration' => 60,
                                    'duration_unit' => 'seconds',
                                    'rest' => 30
                                ]
                            ]
                        ],
                    ]
                ],

                'Tue' => [
                    'workouts' => [
                        [
                            'name' => 'Running',
                            'target_muscles' => ['cardio', 'quads', 'hamstrings', 'glutes', 'calves', 'core'],
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => [
                                    'duration' => 20,
                                    'duration_unit' => 'minutes'
                                ]
                            ]
                        ],
                        [
                            'name' => 'Mountain Climbers',
                            'target_muscles' => ['cardio', 'core', 'abs', 'shoulders', 'chest', 'quads'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 4,
                                    'duration' => 45,
                                    'duration_unit' => 'seconds',
                                    'rest' => 30
                                ]
                            ]
                        ],
                        [
                            'name' => 'Burpees',
                            'target_muscles' => ['cardio', 'full_body', 'chest', 'shoulders', 'triceps', 'core', 'quads', 'glutes'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 4,
                                    'duration' => 30,
                                    'duration_unit' => 'seconds',
                                    'rest' => 30
                                ]
                            ]
                        ],
                    ]
                ],

                'Wed' => [
                    'workouts' => [
                        [
                            'name' => 'Cycling',
                            'target_muscles' => ['cardio', 'quads', 'hamstrings', 'glutes', 'calves'],
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => [
                                    'duration' => 30,
                                    'duration_unit' => 'minutes'
                                ]
                            ]
                        ],
                        [
                            'name' => 'Jump Squats',
                            'target_muscles' => ['cardio', 'quads', 'glutes', 'hamstrings', 'calves', 'core'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 4,
                                    'duration' => 30,
                                    'duration_unit' => 'seconds',
                                    'rest' => 30
                                ]
                            ]
                        ],
                        [
                            'name' => 'Butt Kicks',
                            'target_muscles' => ['cardio', 'hamstrings', 'quads', 'calves', 'glutes'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 4,
                                    'duration' => 40,
                                    'duration_unit' => 'seconds',
                                    'rest' => 30
                                ]
                            ]
                        ],
                    ]
                ],

                'Thu' => [
                    'workouts' => [
                        [
                            'name' => 'Active Recovery Walk',
                            'target_muscles' => ['active_recovery', 'cardio', 'legs', 'calves', 'glutes'],
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => [
                                    'duration' => 20,
                                    'duration_unit' => 'minutes'
                                ]
                            ]
                        ],
                    ]
                ],

                'Fri' => [
                    'workouts' => [
                        [
                            'name' => 'Rowing Machine',
                            'target_muscles' => ['cardio', 'back', 'lats', 'shoulders', 'arms', 'quads', 'glutes', 'core'],
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => [
                                    'duration' => 20,
                                    'duration_unit' => 'minutes'
                                ]
                            ]
                        ],
                        [
                            'name' => 'Jump Rope',
                            'target_muscles' => ['cardio', 'calves', 'quads', 'hamstrings', 'shoulders', 'core'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 5,
                                    'duration' => 60,
                                    'duration_unit' => 'seconds',
                                    'rest' => 30
                                ]
                            ]
                        ],
                        [
                            'name' => 'Skater Jumps',
                            'target_muscles' => ['cardio', 'glutes', 'quads', 'hamstrings', 'calves', 'core', 'balance'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 4,
                                    'duration' => 40,
                                    'duration_unit' => 'seconds',
                                    'rest' => 30
                                ]
                            ]
                        ],
                    ]
                ],

                'Sat' => [
                    'workouts' => [
                        [
                            'name' => 'Stair Climbing',
                            'target_muscles' => ['cardio', 'quads', 'glutes', 'hamstrings', 'calves'],
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => [
                                    'duration' => 20,
                                    'duration_unit' => 'minutes'
                                ]
                            ]
                        ],
                        [
                            'name' => 'Sprint Intervals',
                            'target_muscles' => ['cardio', 'quads', 'hamstrings', 'glutes', 'calves', 'core'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 8,
                                    'duration' => 20,
                                    'duration_unit' => 'seconds',
                                    'rest' => 40
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
