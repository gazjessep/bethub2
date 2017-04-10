<?php

require_once '../vendor/autoload.php';

if (!isset($argv[1])) {
    exit("Need to add arguments eg. php cli_database.php 'user' 'environment (local or production)'"."\n");
}

if (isset($argv[2])) {
    if ($argv[2] === 'production') {
        // Set config to production
        $config = \Database\Config::getConfig(Database\Config::DB_PROD, $argv[1]);
    } else {
        // Set config to local
        $config = \Database\Config::getConfig(Database\Config::DB_LOCAL, $argv[1]);
    }
} else {
    // Set config to local
    $config = \Database\Config::getConfig(Database\Config::DB_LOCAL, $argv[1]);
}

echo('Starting test...'."\r\n");

// Hard Coded
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

$model = new Testing\Model($config);

foreach (range(1,5,1) as $season_id) {
    echo('Starting testing for season '.(string)$season_id."\r\n");
    $model->testIndex($season_id, $testingParameters);
    echo('Completed testing for season '.(string)$season_id."\r\n");
}

