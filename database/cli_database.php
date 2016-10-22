<?php

include_once('mysql_functions.php');
include_once('index.php');

//echo('Building database...'."\r\n");
//$mysql = new \Database\MySQLFunctions();
//$dbconnection = $mysql->connectMySQLDB();
//
//$mysql->executeSchema($dbconnection);
echo('Inserting season...'."\r\n");
$index = new \Database\Index();

$league_name = 'english_premier_league';
$league_country = 'england';
$league_url = 'england/premier-league-';

foreach (range(2011, 2015, 1) as $year) {
    echo('Inserting '.$league_name.' '.(string)$year.'/'.(string)($year+1)."\r\n");
    $addSeason = $index->addSeason($league_name, $league_country,$league_url, $year);
    if ($addSeason == 'Season_exists') {
        echo((string)$year.'/'.(string)($year+1).' year already added, continuing...'."\r\n");
        continue;
    } else {
        echo($addSeason."\r\n");
    }
}

