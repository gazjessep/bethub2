<?php

//add class for MySQL

function connectMySQLDB () {
	$mySQLcon = new PDO('mysql:host=125.236.205.176;dbname=bethub', 'scott', 'SC0TTbethub');

	return $mySQLcon
}

function checkTableExists($dbcon, $teamname) {
	// check if table exists in MYSQL, return true if it does, return false if it doesn't
	// untested
	$sqlQ = 'SELECT ID FROM team WHERE Name="'.$teamname.'"';

	$dbcon->prepare($sqlQ);
	$dbcon->execute();

	$results = $dbcon->fetchAll();

	if (count($results) == 1) {
		return True;
	} else {
		return False;
	}
}

function createTable($dbcon, $teamname, $country) {
	// create a new table in MYSQL, if one for the team does not yet exist
	// untested
	$tablename = $teamname.'_'.$country;

	$sqlQ = 'CREATE TABLE `bethub`.`games_'.$tablename.'` (
		`ID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`GameNumber` INT(11) NOT NULL,
		`Points` TINYINT(4) NOT NULL,
		`GoalFor` TINYINT(4) NOT NULL,
		`GoalAgainst` TINYINT(4) NOT NULL,
		`GoalDiff` SMALLINT(6) NOT NULL,
		`LeagueID` INT(10) UNSIGNED NOT NULL,
		`TeamID` INT(10) NOT NULL,
		PRIMARY KEY (`ID`),
		UNIQUE INDEX `GameNumber` (`GameNumber`),
		INDEX `LeagueID_chelsea` (`LeagueID`),
		INDEX `TeamID_chelsea` (`TeamID`),
		FOREIGN KEY (`LeagueID`) REFERENCES `league` (`ID`),
		FOREIGN KEY (`TeamID`) REFERENCES `team` (`ID`)
	)
	COLLATE 'latin1_swedish_ci' ENGINE=InnoDB ROW_FORMAT=Compact AUTO_INCREMENT=1';

	$dbcon->prepare($sqlQ);
	$dbcon->execute();
}

$file = fopen("./england_premier-league_2012-2013.csv","r");

while(! feof($file)) {
	 $temp = fgetcsv($file);
	 $csv[] = [
		 'hteam' => $temp[0],
		 'ateam' => $temp[1],
		 'goals_ht' => $temp[2],
		 'goals_at' => $temp[3],
		 'game_date' => new DateTime($temp[4])
	];
}

fclose($file);

// start index code here

$mySQLcon = connectMySQLDB();

foreach ($csv as $game) {
	if (checkTableExists($mySQLcon, $game['hteam'])) {
		//insert game for home team
	} else {
		createTable($mySQLcon, $game['hteam'],'eng');
		// insert game for home team
	}

	if (checkTableExists($mySQLcon, $game['hteam'])) {
		//insert game for away team
	} else {
		createTable($mySQLcon, $game['hteam'],'eng');
		// insert game for away team
	}
}

?>