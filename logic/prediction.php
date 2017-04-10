<?php

namespace Logic;

use Database;
use DateTime;
use PDO;

 include_once('../database/mysql_functions.php');
class PredictGames
{

	function determineWinner ($dbcon, $fixture, $season_id, $testingParameters) {
		$home_team_id = $fixture['home_team_id'];
		$away_team_id = $fixture['away_team_id'];
		$fixture_date_string = $fixture['game_date'];
//        $fixture_id = $fixture['fixture_id'];
        $game_points = $fixture['game_points'];
		
		$mySQL = new Database\MySQLFunctions(Database\Index::DB_LOCAL);

        $leaguePositions = $this->checkLeaguePosition($dbcon, $mySQL, $season_id, $fixture_date_string);
        $form = $this->checkForm($dbcon, $mySQL, $season_id, $fixture_date_string);

        $powerRankings = $this->useAlgorithm($leaguePositions, $form, $testingParameters['lp_weighting'], $testingParameters['form_weighting']);

        // Checking if the game has already been played, if it has we will check the result
        $fixture_date = new DateTime($fixture_date_string);
        $date_today = new DateTime();
        $date_today->modify('-2 days');

        if (floatval(($powerRankings[$home_team_id] * $testingParameters['home_booster']) - $powerRankings[$away_team_id]) > $testingParameters['draw_coefficient']) {
            // Home team wins, need to check whether the game has happened. Need to deal with timezones at some point
            if ($fixture_date > $date_today) {
                return 'Home';
            } else {
                $pred_result = $this->checkResult('home', $game_points);
                $return = [
                    'Prediction' => 'Home',
                    'Correct' => $pred_result
                ];
            }
        } elseif (floatval($powerRankings[$away_team_id] - ($powerRankings[$home_team_id] * $testingParameters['home_booster'])) > $testingParameters['draw_coefficient']) {
            // Away team wins, need to check whether the game has happened. Need to deal with timezones at some point
            if ($fixture_date > $date_today) {
                return 'Away';
            } else {
                $pred_result = $this->checkResult('away', $game_points);
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
                $pred_result = $this->checkResult('draw', $game_points);
                $return = [
                    'Prediction' => 'Draw',
                    'Correct' => $pred_result
                ];
            }
        }
        return $return;
	}
	
	function checkResult($prediction, $game_points) {

        if ($game_points == 3) {
            // Home team won
            if ($prediction == 'home') {
                // Prediction Correct
                return 'Yes';
            } else {
                //Prediction Incorrect
                return 'No';
            }
        } elseif ($game_points == 0) {
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

    function checkLeaguePosition(PDO $dbcon, Database\MySQLFunctions $mySQL, $season_id, $fixture_date_string) {
        // We want to store the result of this query as at the date and season passed - as it returns all teams (as at that date)
        // This essentially brings back a ratio of points won for the entire season (ie. league position)
        $pointsRatio = $mySQL->getPointsRatio($dbcon, $season_id, $fixture_date_string);

        return $pointsRatio;
    }
    function checkForm(PDO $dbcon, Database\MySQLFunctions $mySQL, $season_id, $fixture_date_string) {
        // We want to store the result of this query as at the date and season passed - as it returns all teams (as at that date)
        // This essentially brings back a ratio of points won in the last 4 games (ie. form)
        $pointsRatio = $mySQL->getFormRatio($dbcon, $season_id, $fixture_date_string);

        return $pointsRatio;
    }
    function useAlgorithm($leaguePositions, $form, $lp_weighting = 1.00, $form_weighting = 1.00) {
        // Merge the different coefficients into a final power ranking value
        $powerRankings = [];

        // Calculate the form average in case a team hasn't played a game in the past 40 days
        $totalAverage = 0;
        foreach ($form as $value) {
            $totalAverage += floatval($value);
        }
        $formAverage = $totalAverage/count($form);

        foreach($leaguePositions as $team => $leaguePosition) {
            if (isset($form[$team])) {
                $powerRankings[$team] = ($lp_weighting * floatval($leaguePosition)) + ($form_weighting * floatval($form[$team]));
            } else {
                $powerRankings[$team] = ($lp_weighting * floatval($leaguePosition)) + $formAverage;
            }
        }

        return $powerRankings;
    }
}




?>