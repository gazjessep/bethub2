<?php

//add class for MySQL
function connectMySQLDB () {
	include('config.php');

	try {
		$db = $user['database'];
		$dbhost  = 'mysql:host='. $db['ip'] . ';dbname=' . $db['dbname'];

		$mySQLcon = new PDO($dbhost, $db['username'], $db['password']);
		$mySQLcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return $mySQLcon;
	} catch (PDOException $e) {
		exit('Connection failed: ' . $e->getMessage());
	}
}

function executeSchema ($dbcon) {

	try {
		// create league_index table
		$sqlQ = 'CREATE TABLE `bethub`.`league_index` (
			`league_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			`league_name` VARCHAR(50) NOT NULL,
			`league_country` VARCHAR(50) NOT NULL,
			`league_url` VARCHAR(50) NOT NULL,
			PRIMARY KEY (`league_id`)
		)
		 COLLATE "latin1_swedish_ci" ENGINE=InnoDB ROW_FORMAT=Compact AUTO_INCREMENT=1';

		$sqlResponse = $dbcon->prepare($sqlQ);
		$sqlResponse->execute();

	} catch (PDOException $e) {
		exit('Creation of league_index table failed - '.$e->getMessage());

	try {

		// create season_index table
		$sqlQ = 'CREATE TABLE `bethub`.`season_index` (
			`season_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`season_year` YEAR NOT NULL,
			`league_id` INT(11) UNSIGNED NOT NULL,
			PRIMARY KEY (`season_id`),
			INDEX `league_id_season_index` (`league_id`),
			FOREIGN KEY (`league_id`) REFERENCES `league_index` (`league_id`)
		)
		COLLATE "latin1_swedish_ci" ENGINE=InnoDB ROW_FORMAT=Compact AUTO_INCREMENT=1';

		$sqlResponse = $dbcon->prepare($sqlQ);
		$sqlResponse->execute();

	} catch (PDOException $e) {
		exit('Creation of season_index table failed - '.$e->getMessage());
	}

	try {

		// create team_index table
		$sqlQ = 'CREATE TABLE `bethub`.`team_index` (
			`team_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`team_name` VARCHAR(50) NOT NULL,
			`team_country` VARCHAR(50) NOT NULL,
			`league_id` INT(10) UNSIGNED NOT NULL,
			PRIMARY KEY (`team_id`),
			INDEX `league_id_team` (`league_id`),
			FOREIGN KEY (`league_id`) REFERENCES `league_index` (`league_id`)
		)
		 COLLATE "latin1_swedish_ci" ENGINE=InnoDB ROW_FORMAT=Compact AUTO_INCREMENT=1';

		$sqlResponse = $dbcon->prepare($sqlQ);
		$sqlResponse->execute();

	} catch (PDOException $e) {
		exit('Creation of team_index table failed - '.$e->getMessage());
	}

	try {

		// create fixture_index table
		$sqlQ = 'CREATE TABLE `bethub`.`fixture_index` (
			`fixture_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			`home_team_id` INT(10) UNSIGNED NOT NULL,
			`away_team_id` INT(10) UNSIGNED NOT NULL,
			`game_date` DATE NOT NULL,
			`season_id` INT(10) UNSIGNED NOT NULL,
			PRIMARY KEY (`fixture_id`),
			INDEX `season_id_ibfk_4` (`season_id`),
			INDEX `home_team_id_ibfk_1` (`home_team_id`),
			INDEX `away_team_id_ibfk_1` (`away_team_id`),
			FOREIGN KEY (`season_id`) REFERENCES `season_index` (`season_id`),
			FOREIGN KEY (`home_team_id`) REFERENCES `team_index` (`team_id`),
			FOREIGN KEY (`away_team_id`) REFERENCES `team_index` (`team_id`)
		)
		 COLLATE "latin1_swedish_ci" ENGINE=InnoDB ROW_FORMAT=Compact AUTO_INCREMENT=1';

		$sqlResponse = $dbcon->prepare($sqlQ);
		$sqlResponse->execute();

	} catch (PDOException $e) {
		exit('Creation of fixture_index table failed - '.$e->getMessage());
	}

	try {

		// create home_result_index table
		$sqlQ = 'CREATE TABLE `bethub`.`home_result_index` (
			`game_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			`game_date` DATE NOT NULL,
			`game_points` TINYINT(4) NOT NULL,
			`game_gf` TINYINT(4) NOT NULL,
			`game_ga` TINYINT(4) NOT NULL,
			`game_gd` SMALLINT(6) NOT NULL,
			`season_id` INT(10) UNSIGNED NOT NULL,
			`team_id` INT(10) UNSIGNED NOT NULL,
			`fixture_id` INT(10) UNSIGNED NOT NULL,
			PRIMARY KEY (`game_id`),
			UNIQUE INDEX `fixture_id` (`fixture_id`),
			INDEX `season_id_games` (`season_id`),
			INDEX `team_id_games` (`team_id`),
			FOREIGN KEY (`fixture_id`) REFERENCES `fixture_index` (`fixture_id`),
			FOREIGN KEY (`season_id`) REFERENCES `season_index` (`season_id`),
			FOREIGN KEY (`team_id`) REFERENCES `team_index` (`team_id`)
		)
		 COLLATE "latin1_swedish_ci" ENGINE=InnoDB ROW_FORMAT=Compact AUTO_INCREMENT=1';

		$sqlResponse = $dbcon->prepare($sqlQ);
		$sqlResponse->execute();

	} catch (PDOException $e) {
		exit('Creation of home_result_index table failed - '.$e->getMessage());
	}

	try {

		// create away_result_index table
		$sqlQ = 'CREATE TABLE `bethub`.`away_result_index` (
			`game_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			`game_date` DATE NOT NULL,
			`game_points` TINYINT(4) NOT NULL,
			`game_gf` TINYINT(4) NOT NULL,
			`game_ga` TINYINT(4) NOT NULL,
			`game_gd` SMALLINT(6) NOT NULL,
			`season_id` INT(10) UNSIGNED NOT NULL,
			`team_id` INT(10) UNSIGNED NOT NULL,
			`fixture_id` INT(10) UNSIGNED NOT NULL,
			PRIMARY KEY (`game_id`),
			UNIQUE INDEX `fixture_id` (`fixture_id`),
			INDEX `season_id_games` (`season_id`),
			INDEX `team_id_games` (`team_id`),
			FOREIGN KEY (`fixture_id`) REFERENCES `fixture_index` (`fixture_id`),
			FOREIGN KEY (`season_id`) REFERENCES `season_index` (`season_id`),
			FOREIGN KEY (`team_id`) REFERENCES `team_index` (`team_id`)
		)
		 COLLATE "latin1_swedish_ci" ENGINE=InnoDB ROW_FORMAT=Compact AUTO_INCREMENT=1';

		$sqlResponse = $dbcon->prepare($sqlQ);
		$sqlResponse->execute();

	} catch (PDOException $e) {
		exit('Creation of away_result_index table failed - '.$e->getMessage());
	}

	echo ('All tables created successfully!');
	echo ("\r\n");
}

function insertLeague($dbcon, $league_name, $league_country, $league_url) {

	// insert team into team_index table
	try {
		$sqlQ = 'INSERT INTO league_index
		(league_name, league_country, league_url) VALUES
		("'.$league_name.'","'.$league_country.'","'.$league_url.'")';

		$sqlResponse = $dbcon->prepare($sqlQ);
		$sqlResponse->execute();

		$sqlID = $dbcon->prepare("SELECT LAST_INSERT_ID()");
		$sqlID->execute();
		$league_id = $sqlID->fetch()['LAST_INSERT_ID()'];

		return $league_id;
	} catch (PDOException $e) {
		exit($e->getMessage());
	}
}

function leagueExists($dbcon, $league_name, $country) {
	// check if season exists, if true returns the season_id
	try {
		$sqlQ = 'SELECT league_id FROM league_index WHERE league_name="'.$league_name.'" AND league_country="'.$country.'"';

		$sqlResponse = $dbcon->prepare($sqlQ);
		$sqlResponse->execute();

		$results = $sqlResponse->fetchAll();
	} catch (PDOException $e) {
		exit($e->getMessage());
	}

	if (count($results) == 1) {
		return $results[0]['league_id'];
	} else {
		return false;
	}
}

function seasonExists($dbcon, $league_id, $year) {
	// check if season exists, if true returns the season_id
	try {
		$sqlQ = 'SELECT season_id FROM season_index WHERE league_id="'.$league_id.'" AND season_year="'.$year.'"';

		$sqlResponse = $dbcon->prepare($sqlQ);
		$sqlResponse->execute();

		$results = $sqlResponse->fetchAll();
	} catch (PDOException $e) {
		exit($e->getMessage());
	}

	if (count($results) == 1) {
		return $results[0]['season_id'];
	} else {
		return false;
	}
}

function teamExists($dbcon, $teamname, $country) {
	// check if team exists, if true returns the team_id
	try {
		$sqlQ = 'SELECT team_id FROM team_index WHERE team_name="'.$teamname.'" AND team_country="'.$country.'"';

		$sqlResponse = $dbcon->prepare($sqlQ);
		$sqlResponse->execute();

		$results = $sqlResponse->fetchAll();
	} catch (PDOException $e) {
		exit($e->getMessage());
	}

	if (count($results) == 1) {
		return $results[0]['team_id'];
	} else {
		return false;
	}
}

function insertTeam($dbcon, $teamname, $country, $league_id) {

	// insert team into team_index table
	try {
		$sqlQ = 'INSERT INTO team_index
		(team_name, team_country, league_id) VALUES
		("'.$teamname.'","'.$country.'","'.$league_id.'")';

		$sqlResponse = $dbcon->prepare($sqlQ);
		$sqlResponse->execute();

		$sqlID = $dbcon->prepare("SELECT LAST_INSERT_ID()");
		$sqlID->execute();
		$teamID = $sqlID->fetch()['LAST_INSERT_ID()'];

		return $teamID;
	} catch (PDOException $e) {
		exit($e->getMessage());
	}
	
}

function insertSeason($dbcon, $seasonyear, $league_id) {

	// insert team into team_index table
	try {
		$sqlQ = 'INSERT INTO season_index
		(season_year, league_id) VALUES
		("'.$seasonyear.'","'.$league_id.'")';

		$sqlResponse = $dbcon->prepare($sqlQ);
		$sqlResponse->execute();

		$sqlID = $dbcon->prepare("SELECT LAST_INSERT_ID()");
		$sqlID->execute();
		$seasonID = $sqlID->fetch()['LAST_INSERT_ID()'];

		return $seasonID;
	} catch (PDOException $e) {
		exit($e->getMessage());
	}
}

function insertFixture($dbcon, $game_date, $season_id, $hteam_id, $ateam_id) {

	// insert team into team_index table

	try {
		$sqlQ = 'INSERT INTO `fixture_index`
		(home_team_id, away_team_id, game_date, season_id) VALUES
		("'.$hteam_id.'","'.$ateam_id.'","'.$game_date.'","'.$season_id.'")';

		$sqlResponse = $dbcon->prepare($sqlQ);
		$sqlResponse->execute();

		$sqlID = $dbcon->prepare("SELECT LAST_INSERT_ID()");
		$sqlID->execute();
		$fixtureID = $sqlID->fetch()['LAST_INSERT_ID()'];

		return $fixtureID;
	} catch (PDOException $e) {
		exit($e->getMessage());
	}
}

function insertHomeGame($dbcon, $game_data, $season_id, $team_id, $fixture_id) {

	// insert team into team_index table

	try {
		$sqlQ = 'INSERT INTO `home_result_index`
		(game_date, game_points, game_gf, game_ga, game_gd, season_id, team_id, fixture_id) VALUES
		("'.$game_data['game_date'].'","'.$game_data['homepoints'].'","'.$game_data['goalsfor'].'","'.$game_data['goalsagainst'].'","'.$game_data['goaldifference'].'","'.$season_id.'","'.$team_id.'","'.$fixture_id.'")';

		$sqlResponse = $dbcon->prepare($sqlQ);
		$sqlResponse->execute();

	} catch (PDOException $e) {
		exit($e->getMessage());
	}
}

function insertAwayGame($dbcon, $game_data, $season_id, $team_id, $fixture_id) {

	// insert team into team_index table

	try {
		$sqlQ = 'INSERT INTO `away_result_index`
		(game_date, game_points, game_gf, game_ga, game_gd, season_id, team_id, fixture_id) VALUES
		("'.$game_data['game_date'].'","'.$game_data['homepoints'].'","'.$game_data['goalsfor'].'","'.$game_data['goalsagainst'].'","'.$game_data['goaldifference'].'","'.$season_id.'","'.$team_id.'","'.$fixture_id.'")';

		$sqlResponse = $dbcon->prepare($sqlQ);
		$sqlResponse->execute();

	} catch (PDOException $e) {
		exit($e->getMessage());
	}
}

// get total points for a team as at a date in a given season
function getTotalPoints($dbcon, $season_id, $team_id, $date) {
	// UNTESTED! - if unexpected value returns, i suspect the fetch() isn't working properly

	try {
		$sqlQ = 'SELECT sum(tp.game_points) as total_points
			FROM 
				(SELECT hr.fixture_id, team_id as team_id, hr.game_points
				FROM home_result_index hr
				WHERE hr.team_id="'.$team_id.'" AND hr.game_date < "'.$date.'" AND hr.season_id="'.$season_id.'"
				UNION ALL
				SELECT ar.fixture_id, ar.team_id, ar.game_points
				FROM away_result_index ar
				WHERE ar.team_id="'.$team_id.'" AND ar.game_date < "'.$date.'" AND ar.season_id="'.$season_id.'") tp
			GROUP BY tp.team_id';

		$sqlResponse = $dbcon->prepare($sqlQ);
		$sqlResponse->execute();

		$total_points = $sqlResponse->fetch()['total_points'];

	} catch (PDOException $e) {
		exit($e->getMessage());
	}
}

?>