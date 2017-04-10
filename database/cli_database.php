<?php

include_once('mysql_functions.php');
include_once('index.php');

//echo('Building database...'."\r\n");
//$mysql = new \Database\MySQLFunctions();
//$dbconnection = $mysql->connectMySQLDB();
//
//$mysql->executeSchema($dbconnection);
echo('Inserting season...'."\r\n");
// Pass in the config settings we want to use
$index = new Database\Index(Database\Index::DB_LOCAL);

$league_name = 'romanian_liga-1';
$league_country = 'romania';
$league_url = 'romania/liga-1-';

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

