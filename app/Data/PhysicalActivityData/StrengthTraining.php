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
                    'target_muscles' => ['chest', 'biceps'],
                    'workouts' => [

                        [
                            'name' => 'Barbell Bench Press',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 4, 'reps' => 8, 'rest' => 90]
                            ]
                        ],

                        [
                            'name' => 'Incline Dumbbell Press',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Cable Chest Fly',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Barbell Curl',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Hammer Curl',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Push Ups',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 15, 'rest' => 60]
                            ]
                        ]

                    ]
                ],

                'Tue' => [
                    'target_muscles' => ['back', 'triceps', 'abs'],
                    'workouts' => [

                        [
                            'name' => 'Pull Ups',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 4, 'reps' => 8, 'rest' => 90]
                            ]
                        ],

                        [
                            'name' => 'Bent Over Row',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Tricep Pushdown',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Close Grip Bench Press',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Hanging Leg Raise',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => ['sets' => 3, 'duration' => 30, 'duration_unit' => 'seconds', 'rest' => 45]
                            ]
                        ],

                        [
                            'name' => 'Plank',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => ['duration' => 60, 'duration_unit' => 'seconds']
                            ]
                        ]

                    ]
                ],

                'Wed' => [
                    'target_muscles' => ['legs', 'shoulders'],
                    'workouts' => [

                        [
                            'name' => 'Barbell Squat',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 4, 'reps' => 8, 'rest' => 120]
                            ]
                        ],

                        [
                            'name' => 'Leg Press',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 90]
                            ]
                        ],

                        [
                            'name' => 'Walking Lunges',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Shoulder Press',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Lateral Raises',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 45]
                            ]
                        ],

                        [
                            'name' => 'Burpees',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => ['sets' => 3, 'duration' => 30, 'duration_unit' => 'seconds', 'rest' => 45]
                            ]
                        ]

                    ]
                ],

                'Thu' => [
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
                ],

                'Fri' => [
                    'target_muscles' => ['chest', 'biceps'],
                    'workouts' => [

                        [
                            'name' => 'Decline Bench Press',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 4, 'reps' => 8, 'rest' => 90]
                            ]
                        ],

                        [
                            'name' => 'Incline Push Ups',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Pec Deck Machine',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Preacher Curl',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Concentration Curl',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Diamond Push Ups',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 15, 'rest' => 60]
                            ]
                        ]

                    ]
                ],

                'Sat' => [
                    'target_muscles' => ['back', 'triceps', 'abs'],
                    'workouts' => [

                        [
                            'name' => 'Lat Pulldown',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 4, 'reps' => 10, 'rest' => 90]
                            ]
                        ],

                        [
                            'name' => 'T-Bar Row',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Dips',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Skull Crushers',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Russian Twist',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => ['sets' => 3, 'duration' => 30, 'duration_unit' => 'seconds', 'rest' => 45]
                            ]
                        ],

                        [
                            'name' => 'Bicycle Crunch',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'endurance',
                                'data' => ['duration' => 60, 'duration_unit' => 'seconds']
                            ]
                        ]

                    ]
                ],

                'Sun' => [
                    'target_muscles' => ['legs', 'shoulders'],
                    'workouts' => [

                        [
                            'name' => 'Front Squat',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 4, 'reps' => 8, 'rest' => 120]
                            ]
                        ],

                        [
                            'name' => 'Bulgarian Split Squat',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 90]
                            ]
                        ],

                        [
                            'name' => 'Leg Extension',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Arnold Press',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 10, 'rest' => 60]
                            ]
                        ],

                        [
                            'name' => 'Rear Delt Fly',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => ['sets' => 3, 'reps' => 12, 'rest' => 45]
                            ]
                        ],

                        [
                            'name' => 'Jump Squats',
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => ['sets' => 3, 'duration' => 30, 'duration_unit' => 'seconds', 'rest' => 45]
                            ]
                        ]

                    ]
                ]

            ]

        ];
    }
}
