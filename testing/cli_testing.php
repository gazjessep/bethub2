<?php

include_once('test.php');

echo('Starting test...'."\r\n");

$testingParameters = [
    'draw_coefficient' => [
        'min' => 0.01,
        'max' => 0.25,
        'increment' => 0.01
    ],
    'home_booster' => [
        'min' => 0.7,
        'max' => 1.7,
        'increment' => 0.1
    ],
    'form_weighting' => [
        'min' => 0,
        'max' => 4.0,
        'increment' => 0.1
    ]
];

$class = new Testing\Model();

foreach (range(1,5,1) as $season_id) {
    echo('Starting testing for season '.(string)$season_id."\r\n");
    $class->testIndex($season_id, $testingParameters);
    echo('Completed testing for season '.(string)$season_id."\r\n");
}

