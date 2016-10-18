<?php

include_once('mysql_functions.php');
include_once('index.php');

echo('Building database...'."\r\n");
$mysql = new \Database\MySQLFunctions();
$dbconnection = $mysql->connectMySQLDB();

$mysql->executeSchema($dbconnection);
echo('Inserting season...'."\r\n");
$index = new \Database\Index();

$index->addSeason();
echo('Done!'."\r\n");