<?php

namespace Logic;

use Database;
use DateTime;

// include_once('/../database/mysql_functions.php');
class PredictGames
{
	function determineWinner ($fixture, $draw_coefficient, $season_id) {
		$home_team_id = $fixture['home_team_id'];
		$away_team_id = $fixture['away_team_id'];
		$fixture_date_string = $fixture['game_date'];
        $fixture_id = $fixture['fixture_id'];
		
		$mySQL = new Database\MySQLFunctions();
		$dbcon = $mySQL->connectMySQLDB();

        // We want to store the result of this query as at the date and season passed - as it returns all teams (as at that date)
		$pointsRatio = $mySQL->getPointsRatio($dbcon, $season_id, $fixture_date_string);

        print_r($pointsRatio);
        exit("\r\r");

        $fixture_date = new DateTime($fixture_date_string);
        $date_today = new DateTime();
        $date_today->modify('-2 days');

        if (floatval($pointsRatio[$home_team_id]['ratio'] - $pointsRatio[$away_team_id]['ratio']) > $draw_coefficient) {
            // Home team wins, need to check whether the game has happened. Need to deal with timezones at some point
            if ($fixture_date > $date_today) {
                return 'Home';
            } else {
                $pred_result = $this->checkResult($dbcon, $mySQL, $fixture_id, 'home');
                $return = [
                    'Prediction' => 'Home',
                    'Correct' => $pred_result
                ];
            }
        } elseif (floatval($pointsRatio[$away_team_id]['ratio'] - $pointsRatio[$home_team_id]['ratio']) > $draw_coefficient) {
            // Away team wins, need to check whether the game has happened. Need to deal with timezones at some point
            if ($fixture_date > $date_today) {
                return 'Away';
            } else {
                $pred_result = $this->checkResult($dbcon, $mySQL, $fixture_id, 'away');
                $return = [
                    'Prediction' => 'Away',
                    'Correct' => $pred_result
                ];
            }
        } else {
            // Draw, need to check whether the game has happened. Need to deal with timezones at some point
            if ($fixture_date > $date_today) {
                return 'Draw';
            } else {
                $pred_result = $this->checkResult($dbcon, $mySQL, $fixture_id, 'draw');
                $return = [
                    'Prediction' => 'Draw',
                    'Correct' => $pred_result
                ];
            }
        }
        return $return;
	}
	
	function checkResult($dbcon, Database\MySQLFunctions $mySQL, $fixture_id, $prediction) {
		$result = $mySQL->getResult($dbcon, $fixture_id);

        if ($result['game_points'] == 3) {
            // Home team won
            if ($prediction == 'home') {
                // Prediction Correct
                return 'Yes';
            } else {
                //Prediction Incorrect
                return 'No';
            }
        } elseif ($result['game_points'] == 0) {
            // Away team won
            if ($prediction == 'away') {
                // Prediction Correct
                return 'Yes';
            } else {
                //Prediction Incorrect
                return 'No';
            }
        } else {
            // Draw
            if ($prediction == 'draw') {
                // Prediction Correct
                return 'Yes';
            } else {
                //Prediction Incorrect
                return 'No';
            }
        }
	}
}




?>