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
                    'target_muscles' => ['chest', 'shoulders', 'triceps'],
                    'workouts' => [

                        [
                            'name' => 'Push Ups',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 10,
                                    'rest' => 60
                                ]
                            ]
                        ]

                    ]
                ],

                'Tue' => [
                    'target_muscles' => ['back', 'biceps'],
                    'workouts' => [

                        [
                            'name' => 'Pull Ups',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 10,
                                    'rest' => 60
                                ]
                            ]
                        ]

                    ]
                ],

                'Wed' => [
                    'target_muscles' => ['legs', 'glutes'],
                    'workouts' => [

                        [
                            'name' => 'Bodyweight Squats',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'strength',
                                'data' => [
                                    'sets' => 3,
                                    'reps' => 15,
                                    'rest' => 60
                                ]
                            ]
                        ]

                    ]
                ],

                'Thu' => [
                    'target_muscles' => ['core'],
                    'workouts' => [

                        [
                            'name' => 'Plank',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 40,
                                    'duration_unit' => 'seconds',
                                    'rest' => 30
                                ]
                            ]
                        ]

                    ]
                ],

                'Fri' => [
                    'target_muscles' => ['chest', 'shoulders', 'triceps'],
                    'workouts' => [

                        [
                            'name' => 'Decline Push Ups',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 30,
                                    'duration_unit' => 'seconds',
                                    'rest' => 45
                                ]
                            ]
                        ]

                    ]
                ],

                'Sat' => [
                    'target_muscles' => ['back', 'biceps'],
                    'workouts' => [

                        [
                            'name' => 'Wide Grip Pull Ups',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 20,
                                    'duration_unit' => 'seconds',
                                    'rest' => 45
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
