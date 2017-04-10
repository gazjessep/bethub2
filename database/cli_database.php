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

// Instantiate index class
$index = new Database\Index($config);

$league_name = 'english_premier_league';
$league_country = 'england';
$league_url = 'england/premier-league-';

foreach (range(2014, 2016, 1) as $year) {
    echo('Inserting '.$league_name.' '.(string)$year.'/'.(string)($year+1)."\r\n");
    $addSeason = $index->addSeason($league_name, $league_country,$league_url, $year);
    if ($addSeason == 'Season_exists') {
        echo((string)$year.'/'.(string)($year+1).' year already added, continuing...'."\r\n");
        continue;
    } else {
        echo($addSeason."\r\n");
    }
}

