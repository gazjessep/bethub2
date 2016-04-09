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
		echo('Connection failed: ' . $e->getMessage());
	}
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