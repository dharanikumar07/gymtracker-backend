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
                    'target_muscles' => ['core', 'abs'],
                    'workouts' => [

                        [
                            'name' => 'Plank',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 10,
                                    'rest' => 30
                                ]
                            ]
                        ]

                    ]
                ],

                'Tue' => [
                    'target_muscles' => ['balance', 'legs', 'core'],
                    'workouts' => [

                        [
                            'name' => 'Single Leg Balance',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => [
                                    'duration' => 3,
                                    'duration_unit' => 'minutes'
                                ]
                            ]
                        ]

                    ]
                ],

                'Wed' => [
                    'target_muscles' => ['obliques', 'core'],
                    'workouts' => [

                        [
                            'name' => 'Russian Twist',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 15,
                                    'rest' => 30
                                ]
                            ]
                        ]

                    ]
                ],

                'Thu' => [
                    'target_muscles' => ['core'],
                    'workouts' => [

                        [
                            'name' => 'Glute Bridge',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 15,
                                    'rest' => 30
                                ]
                            ]
                        ]

                    ]
                ],

                'Fri' => [
                    'target_muscles' => ['core', 'abs'],
                    'workouts' => [

                        [
                            'name' => 'Plank Shoulder Tap',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 40,
                                    'duration_unit' => 'seconds',
                                    'rest' => 20
                                ]
                            ]
                        ]

                    ]
                ],

                'Sat' => [
                    'target_muscles' => ['functional_core', 'balance'],
                    'workouts' => [

                        [
                            'name' => 'Farmer Carry',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 15,
                                    'rest' => 30
                                ]
                            ]
                        ]

                    ]
                ],

                'Sun' => [
                    'target_muscles' => [],
                    'workouts' => [
                        [
                            'name' => 'Rest Day',
                            'sample_video_link' => '',
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
