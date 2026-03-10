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
                    'target_muscles' => ['full_body'],
                    'workouts' => [

                        [
                            'name' => 'Jump Rope',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 4,
                                    'duration' => 60,
                                    'duration_unit' => 'seconds',
                                    'rest' => 30
                                ]
                            ]
                        ]

                    ]
                ],

                'Tue' => [
                    'target_muscles' => ['legs', 'cardiovascular_system'],
                    'workouts' => [

                        [
                            'name' => 'Running',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 4,
                                    'duration' => 30,
                                    'duration_unit' => 'seconds',
                                    'rest' => 30
                                ]
                            ]
                        ]

                    ]
                ],

                'Wed' => [
                    'target_muscles' => ['legs'],
                    'workouts' => [

                        [
                            'name' => 'Cycling',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 4,
                                    'duration' => 40,
                                    'duration_unit' => 'seconds',
                                    'rest' => 30
                                ]
                            ]
                        ]

                    ]
                ],

                'Thu' => [
                    'target_muscles' => [],
                    'workouts' => [
                        [
                            'name' => 'Active Recovery Walk',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => [
                                    'duration' => 20,
                                    'duration_unit' => 'minutes'
                                ]
                            ]
                        ]
                    ]
                ],

                'Fri' => [
                    'target_muscles' => ['full_body'],
                    'workouts' => [

                        [
                            'name' => 'Rowing Machine',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 4,
                                    'duration' => 40,
                                    'duration_unit' => 'seconds',
                                    'rest' => 30
                                ]
                            ]
                        ]

                    ]
                ],

                'Sat' => [
                    'target_muscles' => ['legs', 'cardio'],
                    'workouts' => [

                        [
                            'name' => 'Stair Climbing',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 8,
                                    'duration' => 20,
                                    'duration_unit' => 'seconds',
                                    'rest' => 40
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
