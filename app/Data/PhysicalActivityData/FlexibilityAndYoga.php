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
                    'target_muscles' => ['full_body'],
                    'workouts' => [

                        [
                            'name' => 'Sun Salutation (Surya Namaskar)',
                            'sample_video_link' => '',
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
                            'name' => 'Child Pose',
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

                'Tue' => [
                    'target_muscles' => ['hips', 'hamstrings'],
                    'workouts' => [

                        [
                            'name' => 'Standing Forward Bend',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                    'target_muscles' => ['spine', 'back'],
                    'workouts' => [

                        [
                            'name' => 'Cat Cow Stretch',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 30,
                                    'duration_unit' => 'seconds',
                                    'rest' => 15
                                ]
                            ]
                        ]

                    ]
                ],

                'Thu' => [
                    'target_muscles' => ['shoulders', 'neck'],
                    'workouts' => [

                        [
                            'name' => 'Neck Stretch',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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

                'Fri' => [
                    'target_muscles' => ['hips', 'glutes'],
                    'workouts' => [

                        [
                            'name' => 'Pigeon Pose',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 4,
                                    'duration' => 25,
                                    'duration_unit' => 'seconds',
                                    'rest' => 15
                                ]
                            ]
                        ]

                    ]
                ],

                'Sat' => [
                    'target_muscles' => ['full_body'],
                    'workouts' => [

                        [
                            'name' => 'Sun Salutation Flow',
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
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
                            'sample_video_link' => '',
                            'metrics' => [
                                'type' => 'timed_sets',
                                'data' => [
                                    'sets' => 3,
                                    'duration' => 30,
                                    'duration_unit' => 'seconds',
                                    'rest' => 15
                                ]
                            ]
                        ]

                    ]
                ],

                'Sun' => [
                    'target_muscles' => [],
                    'workouts' => [
                        [
                            'name' => 'Yoga Nidra (Deep Relaxation)',
                            'sample_video_link' => '',
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
