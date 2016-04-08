<?php

//add class for MySQL

//add class for MySQL
function connectMySQLDB () {
	include('config.php');
	$db = $user['database'];
	$dbhost  = 'mysql:host=' . $db['ip'] . ';dbname=' . $db['dbname'];

	$mySQLcon = new PDO($dbhost, $db['username'], $db['password']);

	return $mySQLcon;
}

function checkTableExists($dbcon, $teamname, $country) {
	// check if table exists in MYSQL, return true if it does, return false if it doesn't, if we want to make this more efficient in future, we only check
	// this until we have created a table the same amount of times as there are teams in the league

	$sqlQ = 'SELECT team_id FROM team_index WHERE team_name="'.$teamname.'"';

	$sqlResponse = $dbcon->prepare($sqlQ);
	$sqlResponse->execute();

	$results = $sqlResponse->fetchAll();

	if (count($results) == 1) {
		return $results[0]['team_id'];
	} else {
		return false;
	}
}

function createTable($dbcon, $teamname, $country, $league_id) {

	// insert team into team_index table

	$sqlQteam = 'INSERT INTO team_index
	(team_name, league_id) VALUES
	("'.$teamname.'","'.$league_id.'")';

	$sqlResponse = $dbcon->prepare($sqlQteam);
	$sqlResponse->execute();

	$teamID = $sqlResponse->fetch()['LAST_INSERT_ID()'];

	// create a new table in MYSQL, if one for the team does not yet exist

	$tablename = strtolower($teamname).'_'.strtolower($country);

	$sqlQ = 'CREATE TABLE `bethub`.`games_'.$tablename.'` (
	`game_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`game_date` DATE NOT NULL,
	`game_points` TINYINT(4) NOT NULL,
	`game_gf` TINYINT(4) NOT NULL,
	`game_ga` TINYINT(4) NOT NULL,
	`game_gd` SMALLINT(6) NOT NULL,
	`season_id` INT(10) UNSIGNED NOT NULL,
	`team_id` INT(10) UNSIGNED NOT NULL,
	PRIMARY KEY (`game_id`),
	INDEX `season_id_games` (`season_id`),
	INDEX `team_id_games` (`team_id`),
	FOREIGN KEY (`season_id`) REFERENCES `season_index` (`season_id`),
	FOREIGN KEY (`team_id`) REFERENCES `team_index` (`team_id`))
 	COLLATE "latin1_swedish_ci" ENGINE=InnoDB ROW_FORMAT=Compact AUTO_INCREMENT=1';

	$sqlStmt = $dbcon->prepare($sqlQ);
	$sqlStmt->execute();

	$tableData = [
		'teamID' => $teamID,
		'tablename' => $tablename
	];

	return $tableData;
}

// start index code here

$file = fopen("./england_premier-league_2012-2013.csv","r");

while(! feof($file)) {
	 $temp = fgetcsv($file);
	 $csv[] = [
		 'hteam' => $temp[0],
		 'ateam' => $temp[1],
		 'goals_ht' => $temp[2],
		 'goals_at' => $temp[3],
		 'game_date' => date_format(new DateTime($temp[4]), "Y-m-d")
	];
}

fclose($file);

include_once('crawler.php');

$games = crawlUrl('http://www.betexplorer.com/soccer/england/premier-league-2013-2014/results/');
echo count($games) . ' games' . PHP_EOL;
var_dump($games[count($games)-1]);

$mySQLcon = connectMySQLDB();

foreach ($games as $game) {

	// set points for each team
	if ($game['goals_ht'] > $game['goals_at']) {
		$homepoints = 3;
		$awaypoints = 0;
	} elseif ($game['goals_at'] > $game['goals_ht']) {
		$homepoints = 0;
		$awaypoints = 3;
	} else {
		$homepoints = 1;
		$awaypoints = 1;
	}
	$league_id = '1';
	

	if ($teamID = checkTableExists($mySQLcon, $game['hteam'], 'eng')) {
		//insert game for home team
		$tablename = strtolower($game['hteam']).'_eng';
		$sqlQ = 'INSERT INTO `games_'.$tablename.'`
		(game_date, game_points, game_gf, game_ga, game_gd, season_id, team_id) VALUES
		("'.$game['game_date'].'","'.$homepoints.'","'.$game['goals_ht'].'","'.$game['goals_at'].'","'.($game['goals_ht']-$game['goals_at']).'","'.$league_id.'","'.$teamID.'")';

		$sqlResponse = $mySQLcon->prepare($sqlQ);
		$sqlResponse->execute();
	} else {
		$sqlReturnsHome = createTable($mySQLcon, $game['hteam'],'eng', $league_id);

		$sqlQ = 'INSERT INTO `games_'.$sqlReturnsHome['tablename'].'`
		(game_date, game_points, game_gf, game_ga, game_gd, season_id, team_id) VALUES
		("'.$game['game_date'].'","'.$homepoints.'","'.$game['goals_ht'].'","'.$game['goals_at'].'","'.($game['goals_ht']-$game['goals_at']).'","'.$league_id.'","'.$sqlReturnsHome['teamID'].'")';

		$sqlResponse = $mySQLcon->prepare($sqlQ);
		$sqlResponse->execute();
	}

	if ($teamID = checkTableExists($mySQLcon, $game['ateam'], 'eng')) {
		//insert game for away team
		$tablename = strtolower($game['ateam']).'_eng';
		$sqlQ = 'INSERT INTO `games_'.$tablename.'`
		(game_date, game_points, game_gf, game_ga, game_gd, season_id, team_id) VALUES
		("'.$game['game_date'].'","'.$awaypoints.'","'.$game['goals_at'].'","'.$game['goals_ht'].'","'.($game['goals_at']-$game['goals_ht']).'","'.$league_id.'","'.$teamID.'")';

		$sqlResponse = $mySQLcon->prepare($sqlQ);
		$sqlResponse->execute();
	} else {
		// insert game for away team
		$sqlReturnsAway = createTable($mySQLcon, $game['ateam'],'eng', $league_id);
		
		$sqlQ = 'INSERT INTO `games_'.$sqlReturnsAway['tablename'].'`
		(game_date, game_points, game_gf, game_ga, game_gd, season_id, team_id) VALUES
		("'.$game['game_date'].'","'.$awaypoints.'","'.$game['goals_at'].'","'.$game['goals_ht'].'","'.($game['goals_at']-$game['goals_ht']).'","'.$league_id.'","'.$sqlReturnsAway['teamID'].'")';

		$sqlResponse = $mySQLcon->prepare($sqlQ);
		$sqlResponse->execute();
	}
}

?>