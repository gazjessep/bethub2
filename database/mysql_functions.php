<?php
namespace Database;

use PDO;
use PDOException;
include_once('crawler.php');
include_once('index.php');
include_once('config.php');

class MySQLFunctions
{
    //add class for MySQL
    function connectMySQLDB () {

        try {
            $user = Config::$mySQL_config_local;
            $db = $user['database'];
            $dbhost  = 'mysql:host='. $db['ip'] . ';port=' . $db['port'] . ';dbname=' . $db['dbname'];

            $mySQLcon = new PDO($dbhost, $db['username'], $db['password']);
            $mySQLcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $mySQLcon;
        } catch (PDOException $e) {
            exit('Connection failed: ' . $e->getMessage());
        }
    }

    function executeSchema (PDO $dbcon) {

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
        }

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

        try {

            // create test_results table
            $sqlQ = 'CREATE TABLE `testing_index` (
                `testing_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `season_id` INT(10) UNSIGNED NOT NULL DEFAULT \'0\',
                `draw_coefficient` DECIMAL(5,2) NOT NULL DEFAULT \'0.00\',
                `home_booster` DECIMAL(5,2) NOT NULL DEFAULT \'0.00\',
                `lp_weighting` DECIMAL(5,2) NOT NULL DEFAULT \'0.00\',
                `form_weighting` DECIMAL(5,2) NOT NULL DEFAULT \'0.00\',
                PRIMARY KEY (`testing_id`),
                INDEX `season_id` (`season_id`),
                CONSTRAINT `FK_testing_index_season_index` FOREIGN KEY (`season_id`) REFERENCES `season_index` (`season_id`)
                )
            COLLATE=\'latin1_swedish_ci\'
            ENGINE=InnoDB
            ROW_FORMAT=COMPACT
            AUTO_INCREMENT=1
            ;';

            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

        } catch (PDOException $e) {
            exit('Creation of testing_index table failed - '.$e->getMessage());
        }

        try {

            // create test_results table
            $sqlQ = 'CREATE TABLE `testing_result_index` (
                `testing_result_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `testing_id` INT(10) UNSIGNED NOT NULL DEFAULT \'0\',
                `home_total` INT(5) NOT NULL DEFAULT \'0\',
                `home_correct` INT(5) NOT NULL DEFAULT \'0\',
                `home_ratio` DECIMAL(5,2) NOT NULL DEFAULT \'0.00\',
                `away_total` INT(5) NOT NULL DEFAULT \'0\',
                `away_correct` INT(5) NOT NULL DEFAULT \'0\',
                `away_ratio` DECIMAL(5,2) NOT NULL DEFAULT \'0.00\',
                `draw_total` INT(5) NOT NULL DEFAULT \'0\',
                `draw_correct` INT(5) NOT NULL DEFAULT \'0\',
                `draw_ratio` DECIMAL(5,2) NOT NULL DEFAULT \'0.00\',
                `total_all` INT(5) NOT NULL DEFAULT \'0\',
                `total_correct` INT(5) NOT NULL DEFAULT \'0\',
                `total_ratio` DECIMAL(5,2) NOT NULL DEFAULT \'0.00\',
                PRIMARY KEY (`testing_result_id`),
                INDEX `testing_id` (`testing_id`),
                CONSTRAINT `FK_test_results_testing_index` FOREIGN KEY (`testing_id`) REFERENCES `testing_index` (`testing_id`)
            )
            COLLATE=\'latin1_swedish_ci\'
            ENGINE=InnoDB
            ROW_FORMAT=COMPACT
            AUTO_INCREMENT=1
            ;
            ';

            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

        } catch (PDOException $e) {
            exit('Creation of testing_result_index table failed - '.$e->getMessage());
        }

        echo ('All tables created successfully!');
        echo ("\r\n");
    }

    function insertLeague(PDO $dbcon, $league_name, $league_country, $league_url) {

        // insert team into team_index table
        try {
            $sqlQ = "INSERT INTO league_index
		(league_name, league_country, league_url) VALUES
		('".$league_name."','".$league_country."','".$league_url."')";

            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

            $sqlID = $dbcon->prepare("SELECT LAST_INSERT_ID()");
            $sqlID->execute();
            $league_id = $sqlID->fetch()['LAST_INSERT_ID()'];

            return $league_id;
        } catch (PDOException $e) {
            exit('Error inserting league : '.$e->getMessage());
        }
    }

    function leagueExists(PDO $dbcon, $league_name, $country) {
        // check if season exists, if true returns the season_id
        try {
            $sqlQ = "SELECT league_id FROM league_index WHERE league_name='".$league_name."' AND league_country='".$country."'";

            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

            $results = $sqlResponse->fetchAll();
        } catch (PDOException $e) {
            exit('Error checking league exists : '.$e->getMessage());
        }

        if (count($results) == 1) {
            return $results[0]['league_id'];
        } else {
            return false;
        }
    }

    function seasonExists(PDO $dbcon, $league_id, $year) {
        // check if season exists, if true returns the season_id
        try {
            $sqlQ = "SELECT season_id FROM season_index WHERE league_id='".$league_id."' AND season_year='".$year."'";

            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

            $results = $sqlResponse->fetchAll();
        } catch (PDOException $e) {
            exit('Error checking season exists : '.$e->getMessage());
        }

        if (count($results) == 1) {
            return $results[0]['season_id'];
        } else {
            return false;
        }
    }

    function teamExists(PDO $dbcon, $teamname, $country) {
        // check if team exists, if true returns the team_id
        try {
            $sqlQ = "SELECT team_id FROM team_index WHERE team_name='".$teamname."' AND team_country='".$country."'";

            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

            $results = $sqlResponse->fetchAll();
        } catch (PDOException $e) {
            exit('Error checking team exists : '.$e->getMessage());
        }

        if (count($results) == 1) {
            return $results[0]['team_id'];
        } else {
            return false;
        }
    }

    function insertTeam(PDO $dbcon, $teamname, $country, $league_id) {

        // insert team into team_index table
        try {
            $sqlQ = "INSERT INTO team_index
		(team_name, team_country, league_id) VALUES
		('".$teamname."','".$country."','".$league_id."')";

            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

            $sqlID = $dbcon->prepare("SELECT LAST_INSERT_ID()");
            $sqlID->execute();
            $teamID = $sqlID->fetch()['LAST_INSERT_ID()'];

            return $teamID;
        } catch (PDOException $e) {
            exit('Error inserting team : '.$e->getMessage());
        }

    }

    function insertSeason(PDO $dbcon, $seasonyear, $league_id) {

        // insert team into team_index table
        try {
            $sqlQ = "INSERT INTO season_index
		(season_year, league_id) VALUES
		('".$seasonyear."','".$league_id."')";

            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

            $sqlID = $dbcon->prepare("SELECT LAST_INSERT_ID()");
            $sqlID->execute();
            $seasonID = $sqlID->fetch()['LAST_INSERT_ID()'];

            return $seasonID;
        } catch (PDOException $e) {
            exit('Error inserting season : '.$e->getMessage());
        }
    }

    function insertFixture(PDO $dbcon, $game_date, $season_id, $hteam_id, $ateam_id) {

        // insert team into team_index table

        try {
            $sqlQ = "INSERT INTO `fixture_index`
		(home_team_id, away_team_id, game_date, season_id) VALUES
		('".$hteam_id."','".$ateam_id."','".$game_date."','".$season_id."')";

            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

            $sqlID = $dbcon->prepare("SELECT LAST_INSERT_ID()");
            $sqlID->execute();
            $fixtureID = $sqlID->fetch()['LAST_INSERT_ID()'];

            return $fixtureID;
        } catch (PDOException $e) {
            exit('Error inserting fixture : '.$e->getMessage());
        }
    }

    function insertHomeGame(PDO $dbcon, $game_data, $season_id, $team_id, $fixture_id) {

        // insert team into team_index table

        try {
            $sqlQ = "INSERT INTO `home_result_index`
		(game_date, game_points, game_gf, game_ga, game_gd, season_id, team_id, fixture_id) VALUES
		('".$game_data['game_date']."','".$game_data['homepoints']."','".$game_data['goalsfor']."','".$game_data['goalsagainst']."','".$game_data['goaldifference']."','".$season_id."','".$team_id."','".$fixture_id."')";

            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

        } catch (PDOException $e) {
            exit('Error inserting home game : '.$e->getMessage());
        }
    }

    function insertAwayGame(PDO $dbcon, $game_data, $season_id, $team_id, $fixture_id) {

        // insert team into team_index table

        try {
            $sqlQ = "INSERT INTO `away_result_index`
		(game_date, game_points, game_gf, game_ga, game_gd, season_id, team_id, fixture_id) VALUES
		('".$game_data['game_date']."','".$game_data['homepoints']."','".$game_data['goalsfor']."','".$game_data['goalsagainst']."','".$game_data['goaldifference']."','".$season_id."','".$team_id."','".$fixture_id."')";

            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

        } catch (PDOException $e) {
            exit('Error inserting away game : '.$e->getMessage());
        }
    }

    function getTotalPoints(PDO $dbcon, $season_id, $team_id, $date) {
        // get season results/fixtures

        try {
            $sqlQ = "SELECT sum(tp.game_points) as total_points
			FROM 
				(SELECT hr.fixture_id, team_id as team_id, hr.game_points
				FROM home_result_index hr
				WHERE hr.team_id='".$team_id."' AND hr.game_date < '".$date."' AND hr.season_id='".$season_id."'
				UNION ALL
				SELECT ar.fixture_id, ar.team_id, ar.game_points
				FROM away_result_index ar
				WHERE ar.team_id='".$team_id."' AND ar.game_date < '".$date."' AND ar.season_id='".$season_id."') tp
			GROUP BY tp.team_id";

            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

            $total_points = $sqlResponse->fetch()['total_points'];
            return $total_points;

        } catch (PDOException $e) {
            exit('Error getting total points : '.$e->getMessage());
        }
    }
    // get all fixtures within a season
    function getSeasonFixtures(PDO $dbcon, $season_id ) {

        try {
            $sqlQ = "SELECT fi.fixture_id, fi.home_team_id, fi.away_team_id, fi.game_date, hr.game_points
            FROM fixture_index fi
            INNER JOIN home_result_index hr
            ON fi.fixture_id = hr.fixture_id
            WHERE fi.season_id='".$season_id."
            'ORDER BY fi.game_date ASC";

            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

            $fixtures = $sqlResponse->fetchAll();
            return $fixtures;

        } catch (PDOException $e) {
            exit('Error getting season fixtures : '.$e->getMessage());
        }
    }

	function getTeamsListForSeason(PDO $dbcon, $season_id) {
		try {
			$sqlQ = 'SELECT DISTINCT home_team_id
				FROM fixture_index
			WHERE season_id=' . $season_id;

            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

            $teamsList = $sqlResponse->fetchAll();

            return $teamsList;

        } catch (PDOException $e) {
            exit('Error getting teams : '.$e->getMessage());
        }
	}

    function getPointsRatio(PDO $dbcon, $season_id, $fixture_date) {
        try {
            $sqlQ = "SELECT tp.team_id, sum(tp.game_points)/(count(tp.game_points)*3) as ratio
			    FROM 
				(SELECT hr.fixture_id, team_id as team_id, hr.game_points
				FROM home_result_index hr
				WHERE hr.game_date < '".$fixture_date."' AND hr.season_id='".$season_id."'
				UNION ALL
				SELECT ar.fixture_id, ar.team_id, ar.game_points
				FROM away_result_index ar
				WHERE ar.game_date < '".$fixture_date."' AND ar.season_id='".$season_id."') tp
			    GROUP BY tp.team_id";

            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

            $pointsRatio = $sqlResponse->fetchAll(PDO::FETCH_KEY_PAIR);

            return $pointsRatio;

        } catch (PDOException $e) {
            exit('Error getting points ratio : '.$e->getMessage());
        }
    }

    function getFormRatio(PDO $dbcon, $season_id, $fixture_date) {
        // Get the fixture date and filter results in the past 28 days
        $fixture_date_time = new \DateTime($fixture_date);
        $filter_date_time = $fixture_date_time->modify('-40 days');
        $filter_date = $filter_date_time->format('Y-m-d');
        try {
            $sqlQ = "SELECT tp.team_id, sum(tp.game_points)/(count(tp.game_points)*3) as ratio
			    FROM 
				(SELECT hr.fixture_id, hr.team_id, hr.game_points
				FROM home_result_index hr
				WHERE hr.game_date > '".$filter_date."' AND hr.game_date < '".$fixture_date."' AND hr.season_id='".$season_id."'
				UNION ALL
				SELECT ar.fixture_id, ar.team_id, ar.game_points
				FROM away_result_index ar
				WHERE ar.game_date > '".$filter_date."' AND ar.game_date < '".$fixture_date."' AND ar.season_id='".$season_id."') tp
			    GROUP BY tp.team_id";

            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

            $pointsRatio = $sqlResponse->fetchAll(PDO::FETCH_KEY_PAIR);

            return $pointsRatio;

        } catch (PDOException $e) {
            exit('Error getting form ratio : '.$e->getMessage());
        }
    }

    // Gets the result from the home teams perspective
    function getResult(PDO $dbcon, $fixture_id) {
        try {
            $sqlQ = "SELECT *
                FROM home_result_index hr
                WHERE hr.fixture_id='".$fixture_id."'";

            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

            $result = $sqlResponse->fetch();

            return $result;

        } catch (PDOException $e) {
            exit('Error getting result : '.$e->getMessage());
        }
    }

    // Store the results of predicted season
    function storeTestingParameters(PDO $dbcon, $testingParameters) {
        try {
            $sqlQ = "INSERT INTO `testing_index`
		        (season_id, draw_coefficient, home_booster, lp_weighting, form_weighting) VALUES
		        ('".$testingParameters['season_id']."','".$testingParameters['draw_coefficient']."','".$testingParameters['home_booster']."','".$testingParameters['lp_weighting'].
                "','".$testingParameters['form_weighting']."')";
            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

            $sqlID = $dbcon->prepare("SELECT LAST_INSERT_ID()");
            $sqlID->execute();
            $testingID = $sqlID->fetch()['LAST_INSERT_ID()'];

            return $testingID;

        } catch (PDOException $e) {
            exit('Error storing predictions : '.$e->getMessage());
        }
    }

    // Store the results of predicted season
    function storePredictions(PDO $dbcon, $testing_id, $results) {
        try {
            $sqlQ = "INSERT INTO `testing_result_index`
		        (testing_id, home_total, home_correct, home_ratio, away_total, away_correct, away_ratio, draw_total, draw_correct, draw_ratio, total_all, total_correct, total_ratio) VALUES
		        ('".$testing_id."','".$results['Home']['Games']."','".$results['Home']['Correct']."','".$results['Home']['Ratio Correct'].
                "','".$results['Away']['Games']."','".$results['Away']['Correct']."','".$results['Away']['Ratio Correct']."','".$results['Draw']['Games']."','".
                $results['Draw']['Correct']."','".$results['Draw']['Ratio Correct']."','".$results['Total']['Games']. "','".$results['Total']['Correct']."','".$results['Total']['Ratio Correct']."')";
            $sqlResponse = $dbcon->prepare($sqlQ);
            $sqlResponse->execute();

        } catch (PDOException $e) {
            exit('Error storing predictions : '.$e->getMessage());
        }
    }
}

?>