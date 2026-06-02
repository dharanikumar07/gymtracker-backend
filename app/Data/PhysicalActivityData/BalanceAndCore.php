<?php

namespace App\Data\PhysicalActivityData;

class BalanceAndCore extends AbstractPhysicalActivity
{
    public function getData(): array
    {
        return [
            'units' => $this->getAvailableUnitTypes(),

            'metrics_types' => $this->getAvailableMetricTypes(),

            'physical_activity_type' => 'balance_core',

            'balance_core' => [

                'Mon' => [
                    'workouts' => [
                        [
                            'name' => 'Plank',
                            'target_muscles' => ['core', 'abs', 'shoulders', 'lower_back'],
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => [
                                    'duration' => 60,
                                    'duration_unit' => 'seconds'
                                ]
                            ]
                        ],
                        [
                            'name' => 'Dead Bug',
                            'target_muscles' => ['core', 'abs', 'hip_flexors', 'lower_back'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 12,
                                    'rest' => 30
                                ]
                            ]
                        ],
                        [
                            'name' => 'Bird Dog',
                            'target_muscles' => ['core', 'lower_back', 'glutes', 'shoulders'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 10,
                                    'rest' => 30
                                ]
                            ]
                        ],
                    ]
                ],

                'Tue' => [
                    'workouts' => [
                        [
                            'name' => 'Single Leg Balance',
                            'target_muscles' => ['balance', 'stability', 'ankles', 'calves', 'core'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 4,
                                    'duration' => 30,
                                    'duration_unit' => 'seconds',
                                    'rest' => 15
                                ]
                            ]
                        ],
                        [
                            'name' => 'Single Leg Deadlift (Bodyweight)',
                            'target_muscles' => ['hamstrings', 'glutes', 'core', 'lower_back', 'balance'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 10,
                                    'rest' => 30
                                ]
                            ]
                        ],
                        [
                            'name' => 'Heel To Toe Walk',
                            'target_muscles' => ['balance', 'stability', 'ankles', 'calves', 'core'],
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => [
                                    'duration' => 3,
                                    'duration_unit' => 'minutes'
                                ]
                            ]
                        ],
                    ]
                ],

                'Wed' => [
                    'workouts' => [
                        [
                            'name' => 'Russian Twist',
                            'target_muscles' => ['obliques', 'abs', 'core', 'hip_flexors'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 20,
                                    'rest' => 30
                                ]
                            ]
                        ],
                        [
                            'name' => 'Side Plank',
                            'target_muscles' => ['obliques', 'core', 'abs', 'shoulders'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 40,
                                    'duration_unit' => 'seconds',
                                    'rest' => 20
                                ]
                            ]
                        ],
                        [
                            'name' => 'Standing Oblique Crunch',
                            'target_muscles' => ['obliques', 'abs', 'core', 'hip_flexors'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 15,
                                    'rest' => 30
                                ]
                            ]
                        ],
                    ]
                ],

                'Thu' => [
                    'workouts' => [
                        [
                            'name' => 'Glute Bridge',
                            'target_muscles' => ['glutes', 'hamstrings', 'core', 'lower_back'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 12,
                                    'rest' => 30
                                ]
                            ]
                        ],
                        [
                            'name' => 'Pelvic Tilt',
                            'target_muscles' => ['core', 'abs', 'lower_back', 'pelvic_floor'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 15,
                                    'rest' => 30
                                ]
                            ]
                        ],
                    ]
                ],

                'Fri' => [
                    'workouts' => [
                        [
                            'name' => 'Plank Shoulder Tap',
                            'target_muscles' => ['core', 'abs', 'shoulders', 'chest', 'triceps'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 20,
                                    'rest' => 30
                                ]
                            ]
                        ],
                        [
                            'name' => 'Side Plank Hip Dip',
                            'target_muscles' => ['obliques', 'core', 'abs', 'shoulders', 'glutes'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 12,
                                    'rest' => 30
                                ]
                            ]
                        ],
                        [
                            'name' => 'Hollow Body Hold',
                            'target_muscles' => ['abs', 'core', 'hip_flexors', 'quads'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 40,
                                    'duration_unit' => 'seconds',
                                    'rest' => 20
                                ]
                            ]
                        ],
                    ]
                ],

                'Sat' => [
                    'workouts' => [
                        [
                            'name' => 'Farmer Carry',
                            'target_muscles' => ['grip', 'forearms', 'traps', 'core', 'shoulders', 'legs'],
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => [
                                    'duration' => 2,
                                    'duration_unit' => 'minutes'
                                ]
                            ]
                        ],
                        [
                            'name' => 'Stability Ball Plank',
                            'target_muscles' => ['core', 'abs', 'shoulders', 'stability', 'lower_back'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 40,
                                    'duration_unit' => 'seconds',
                                    'rest' => 20
                                ]
                            ]
                        ],
                        [
                            'name' => 'Standing Knee Raise',
                            'target_muscles' => ['abs', 'core', 'hip_flexors', 'balance'],
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 15,
                                    'rest' => 30
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
