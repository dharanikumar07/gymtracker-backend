<?php

namespace App\Data\PhysicalActivityData;

class FlexibilityAndYoga extends AbstractPhysicalActivity
{
    public function getData(): array
    {
        return [
            'units' => $this->getAvailableUnitTypes(),

            'metrics_types' => $this->getAvailableMetricTypes(),

            'physical_activity_type' => 'flexibility_yoga',

            'flexibility_yoga' => [

                'Mon' => [
                    'workouts' => [
                        [
                            'name' => 'Sun Salutation (Surya Namaskar)',
                            'target_muscles' => ['full_body', 'mobility', 'flexibility', 'shoulders', 'chest', 'hamstrings', 'core'],
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => [
                                    'duration' => 10,
                                    'duration_unit' => 'minutes'
                                ]
                            ]
                        ],
                        [
                            'name' => 'Downward Dog Stretch',
                            'target_muscles' => ['hamstrings', 'calves', 'shoulders', 'back', 'spine'],
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
                            'name' => 'Child Pose',
                            'target_muscles' => ['lower_back', 'hips', 'shoulders', 'spine', 'recovery'],
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

                'Tue' => [
                    'workouts' => [
                        [
                            'name' => 'Standing Forward Bend',
                            'target_muscles' => ['hamstrings', 'calves', 'lower_back', 'spine'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 30,
                                    'duration_unit' => 'seconds',
                                    'rest' => 15
                                ]
                            ]
                        ],
                        [
                            'name' => 'Low Lunge Stretch',
                            'target_muscles' => ['hip_flexors', 'quads', 'glutes', 'hips', 'hamstrings'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 30,
                                    'duration_unit' => 'seconds',
                                    'rest' => 15
                                ]
                            ]
                        ],
                        [
                            'name' => 'Butterfly Pose',
                            'target_muscles' => ['hips', 'adductors', 'groin', 'lower_back'],
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
                            'name' => 'Cat Cow Stretch',
                            'target_muscles' => ['spine', 'lower_back', 'upper_back', 'core'],
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => [
                                    'duration' => 5,
                                    'duration_unit' => 'minutes'
                                ]
                            ]
                        ],
                        [
                            'name' => 'Cobra Pose',
                            'target_muscles' => ['spine', 'lower_back', 'chest', 'abs', 'shoulders'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 4,
                                    'duration' => 25,
                                    'duration_unit' => 'seconds',
                                    'rest' => 15
                                ]
                            ]
                        ],
                        [
                            'name' => 'Seated Spinal Twist',
                            'target_muscles' => ['spine', 'obliques', 'lower_back', 'hips'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 30,
                                    'duration_unit' => 'seconds',
                                    'rest' => 15
                                ]
                            ]
                        ],
                    ]
                ],

                'Thu' => [
                    'workouts' => [
                        [
                            'name' => 'Neck Stretch',
                            'target_muscles' => ['neck', 'traps', 'upper_back', 'shoulders'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 20,
                                    'duration_unit' => 'seconds',
                                    'rest' => 10
                                ]
                            ]
                        ],
                        [
                            'name' => 'Thread The Needle Pose',
                            'target_muscles' => ['shoulders', 'upper_back', 'spine', 'chest'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 25,
                                    'duration_unit' => 'seconds',
                                    'rest' => 10
                                ]
                            ]
                        ],
                        [
                            'name' => 'Puppy Pose',
                            'target_muscles' => ['shoulders', 'chest', 'upper_back', 'spine', 'lats'],
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

                'Fri' => [
                    'workouts' => [
                        [
                            'name' => 'Pigeon Pose',
                            'target_muscles' => ['hips', 'glutes', 'hip_flexors', 'lower_back'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 40,
                                    'duration_unit' => 'seconds',
                                    'rest' => 15
                                ]
                            ]
                        ],
                        [
                            'name' => 'Happy Baby Pose',
                            'target_muscles' => ['hips', 'hamstrings', 'adductors', 'lower_back'],
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => [
                                    'duration' => 3,
                                    'duration_unit' => 'minutes'
                                ]
                            ]
                        ],
                        [
                            'name' => 'Bridge Pose',
                            'target_muscles' => ['glutes', 'hamstrings', 'lower_back', 'chest', 'hip_flexors'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 4,
                                    'duration' => 25,
                                    'duration_unit' => 'seconds',
                                    'rest' => 15
                                ]
                            ]
                        ],
                    ]
                ],

                'Sat' => [
                    'workouts' => [
                        [
                            'name' => 'Sun Salutation Flow',
                            'target_muscles' => ['full_body', 'mobility', 'flexibility', 'shoulders', 'hamstrings', 'core'],
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => [
                                    'duration' => 15,
                                    'duration_unit' => 'minutes'
                                ]
                            ]
                        ],
                        [
                            'name' => 'Triangle Pose',
                            'target_muscles' => ['hamstrings', 'hips', 'obliques', 'spine', 'shoulders', 'balance'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 30,
                                    'duration_unit' => 'seconds',
                                    'rest' => 15
                                ]
                            ]
                        ],
                        [
                            'name' => 'Warrior II Pose',
                            'target_muscles' => ['quads', 'glutes', 'hips', 'shoulders', 'core', 'balance'],
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 30,
                                    'duration_unit' => 'seconds',
                                    'rest' => 15
                                ]
                            ]
                        ],
                    ]
                ],

                'Sun' => [
                    'workouts' => [
                        [
                            'name' => 'Yoga Nidra (Deep Relaxation)',
                            'target_muscles' => ['recovery', 'relaxation', 'mindfulness', 'nervous_system'],
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => [
                                    'duration' => 15,
                                    'duration_unit' => 'minutes'
                                ]
                            ]
                        ]
                    ]
                ]

            ]
        ];
    }
}
