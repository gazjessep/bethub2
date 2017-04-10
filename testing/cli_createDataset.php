<?php

require_once '../vendor/autoload.php';

if (!isset($argv[1])) {
    exit("Need to add arguments eg. php cli_database.php 'user' 'environment (local or production)' 'season_id'"."\n");
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

echo('Building test dataset...'."\r\n");

$model = new Testing\Model($config);

if (!isset($argv[3])) {
    exit("Use as follows - php cli_testing 'user' 'environment' 'season_id'"."\n");
}

$model->createDataset($argv[1]);