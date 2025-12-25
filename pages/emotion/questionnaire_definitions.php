<?php
/**
 * Shared Questionnaire Definitions
 * Contains question text and configuration for all assessments
 */

// GDS-15 (Geriatric Depression Scale) - Yes/No format
$gds_questions = [
    'Are you basically satisfied with your life?',
    'Have you dropped many of your activities and interests?',
    'Do you feel that your life is empty?',
    'Do you often get bored?',
    'Are you in good spirits most of the time?',
    'Are you afraid that something bad is going to happen to you?',
    'Do you feel happy most of the time?',
    'Do you often feel helpless?',
    'Do you prefer to stay at home, rather than going out and doing new things?',
    'Do you feel you have more problems with memory than most?',
    'Do you think it is wonderful to be alive now?',
    'Do you feel pretty worthless the way you are now?',
    'Do you feel full of energy?',
    'Do you feel that your situation is hopeless?',
    'Do you think that most people are better off than you are?',
    'Do you frequently get upset over little things?',
    'Do you frequently feel like crying?',
    'Do you have trouble concentrating?',
    'Do you enjoy getting up in the morning?',
    'Do you prefer to avoid social gatherings?'
];

// PHQ-9 (Patient Health Questionnaire) - Depression screening
$phq9_questions = [
    'Little interest or pleasure in doing things',
    'Feeling down, depressed, or hopeless',
    'Trouble falling or staying asleep, or sleeping too much',
    'Feeling tired or having little energy',
    'Poor appetite or overeating',
    'Feeling bad about yourself - or that you are a failure or have let yourself or your family down',
    'Trouble concentrating on things, such as reading the newspaper or watching television',
    'Moving or speaking so slowly that other people could have noticed. Or the opposite - being so fidgety or restless that you have been moving around a lot more than usual',
    'Thoughts that you would be better off dead, or of hurting yourself in some way'
];

// GAD-7 (Generalized Anxiety Disorder) - Anxiety screening
$gad7_questions = [
    'Feeling nervous, anxious, or on edge',
    'Not being able to stop or control worrying',
    'Worrying too much about different things',
    'Trouble relaxing',
    'Being so restless that it\'s hard to sit still',
    'Becoming easily annoyed or irritable',
    'Feeling afraid as if something awful might happen'
];

// PSS-4 (Perceived Stress Scale) - Stress assessment
$pss4_questions = [
    'In the last month, how often have you felt that you were unable to control the important things in your life?',
    'In the last month, how often have you felt confident about your ability to handle your personal problems?',
    'In the last month, how often have you felt that things were going your way?',
    'In the last month, how often have you felt difficulties were piling up so high that you could not overcome them?'
];

// WHO-5 (Well-Being Index) - General wellbeing
$who5_questions = [
    'I have felt cheerful and in good spirits',
    'I have felt calm and relaxed',
    'I have felt active and vigorous',
    'I woke up feeling fresh and rested',
    'My daily life has been filled with things that interest me'
];

// Sleep Quality Questions (Pittsburgh Sleep Quality Index inspired)
$sleep_questions = [
    'During the past month, how would you rate your sleep quality overall?',
    'During the past month, how often have you had trouble falling asleep?',
    'During the past month, how often have you had to take medicine to help you sleep?',
    'During the past month, how often have you had trouble staying awake during daytime activities?',
    'During the past month, how much of a problem has it been for you to keep up enthusiasm to get things done?'
];

$questionnaires = [
    'depression' => [
        'title' => 'Depression Screening (GDS-15 Based)',
        'description' => 'These questions are based on the validated Geriatric Depression Scale. Answer honestly about how you\'ve felt recently.',
        'questions' => $gds_questions, // Use all GDS questions
        'format' => 'yes_no'
    ],
    'mood' => [
        'title' => 'Mood Assessment (PHQ-9 Based)',
        'description' => 'Over the last 2 weeks, how often have you been bothered by the following problems?',
        'questions' => $phq9_questions, // Use all PHQ-9 questions
        'format' => 'frequency'
    ],
    'anxiety' => [
        'title' => 'Anxiety Screening (GAD-7 Based)',
        'description' => 'Over the last 2 weeks, how often have you been bothered by the following?',
        'questions' => $gad7_questions, // Use all GAD-7 questions
        'format' => 'frequency'
    ],
    'stress' => [
        'title' => 'Stress Assessment (PSS-4)',
        'description' => 'These questions ask about your feelings and thoughts during the last month.',
        'questions' => $pss4_questions, // All 4 PSS questions
        'format' => 'frequency'
    ],
    'wellbeing' => [
        'title' => 'Well-Being Index (WHO-5)',
        'description' => 'Please indicate for each of the five statements which is closest to how you have been feeling over the last two weeks.',
        'questions' => $who5_questions, // All 5 WHO questions
        'format' => 'scale'
    ],
    'sleep' => [
        'title' => 'Sleep Quality Assessment (PSQI Based)',
        'description' => 'The following questions relate to your usual sleep habits during the past month.',
        'questions' => $sleep_questions, // All sleep questions
        'format' => 'frequency'
    ]
];

// Define answer options based on format
$answer_formats = [
    'yes_no' => [
        ['value' => 1, 'label' => 'Yes'],
        ['value' => 0, 'label' => 'No']
    ],
    'frequency' => [
        ['value' => 0, 'label' => 'Not at all'],
        ['value' => 1, 'label' => 'Several days'],
        ['value' => 2, 'label' => 'More than half the days'],
        ['value' => 3, 'label' => 'Nearly every day']
    ],
    'scale' => [
        ['value' => 5, 'label' => 'All of the time'],
        ['value' => 4, 'label' => 'Most of the time'],
        ['value' => 3, 'label' => 'More than half the time'],
        ['value' => 2, 'label' => 'Less than half the time'],
        ['value' => 1, 'label' => 'Some of the time'],
        ['value' => 0, 'label' => 'At no time']
    ]
];
