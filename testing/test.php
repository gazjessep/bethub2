<?php
namespace Testing;

use Database;
use Logic;
include_once('../database/mysql_functions.php');
include_once('../logic/prediction.php');

class Model
{
    function testIndex() {
        // Season is hardcoded for now
        $season_id = '1';

        // Get the seasons results & fixtures
        $fixtures = $this->getSeason($season_id);

        // Loop through our draw/win cutoff
        foreach (range(0.05, 0.25, 0.01) as $draw_coefficient) {
            foreach (range(0.7, 1.3, 0.1) as $home_booster) {
                $testingID = $this->storeTestingParameters($season_id, $draw_coefficient,$home_booster);
                $results = $this->testPredictions($draw_coefficient, $home_booster, $season_id, $fixtures);
                $this->storePredictions($testingID, $results);
            }
        }
    }
	function testPredictions($draw_coefficient, $home_booster, $season_id, $fixtures) {
	    echo('Testing Predictions for Draw Coefficient of '.$draw_coefficient.'...'."\r\n");
        echo('and Home Booster of '.$home_booster.'...'."\r\n");

        $prediction = new Logic\PredictGames();

		$teamsArray = [];
		$teamsList = $this->getTeamsListForSeason($season_id);

		foreach ($teamsList as $team) {
			$teamsArray[$team['home_team_id']] = 'Placeholder';
		}

		$correctCount_home = 0;
        $totalCount_home = 0;
        $correctCount_away = 0;
        $totalCount_away = 0;
        $correctCount_draw = 0;
        $totalCount_draw = 0;
        $correctCount_all = 0;
        $totalCount_all = 0;

		foreach ($fixtures as $fixture) {
		    // To ensure that all teams have played at least once, before we make a prediction
			if (count($teamsArray) > 0) {
				unset($teamsArray[$fixture['home_team_id']]);
				unset($teamsArray[$fixture['away_team_id']]);
				continue;
			}
			$predictedResult = $prediction->determineWinner($fixture, $draw_coefficient, $home_booster, $season_id);
            if (!empty($predictedResult['Prediction']) && !empty($predictedResult['Correct'])) {
                if ($predictedResult['Prediction'] == 'Home') {
                    $totalCount_all++;
                    $totalCount_home++;
                    if ($predictedResult['Correct'] == 'Yes') {
                        $correctCount_all++;
                        $correctCount_home++;
                    }
                } elseif ($predictedResult['Prediction'] == 'Away') {
                    $totalCount_all++;
                    $totalCount_away++;
                    if ($predictedResult['Correct'] == 'Yes') {
                        $correctCount_all++;
                        $correctCount_away++;
                    }
                } elseif ($predictedResult['Prediction'] == 'Draw') {
                    $totalCount_all++;
                    $totalCount_draw++;
                    if ($predictedResult['Correct'] == 'Yes') {
                        $correctCount_all++;
                        $correctCount_draw++;
                    }
                }
            }
		}
		$resultsArray = [
		    'Total' => [
                'Games' => $totalCount_all,
                'Correct' => $correctCount_all,
                'Incorrect' => $totalCount_all - $correctCount_all,
                'Ratio Correct' => $correctCount_all/$totalCount_all
            ],
            'Home' => [
                'Games' => $totalCount_home,
                'Correct' => $correctCount_home,
                'Incorrect' => $totalCount_home - $correctCount_home,
                'Ratio Correct' => $correctCount_home/$totalCount_home
            ],
            'Away' => [
                'Games' => $totalCount_away,
                'Correct' => $correctCount_away,
                'Incorrect' => $totalCount_away - $correctCount_away,
                'Ratio Correct' => $correctCount_away/$totalCount_away
            ],
            'Draw' => [
                'Games' => $totalCount_draw,
                'Correct' => $correctCount_draw,
                'Incorrect' => $totalCount_draw - $correctCount_draw,
                'Ratio Correct' => $correctCount_draw/$totalCount_draw
            ]
        ];
        return $resultsArray;
	}
	
	function getSeason($season_id) {
		$mySQL = new Database\MySQLFunctions();
		$dbcon = $mySQL->connectMySQLDB();
		
		$fixtures = $mySQL->getSeasonFixtures($dbcon, $season_id);
		
		return $fixtures;
	}

	function getTeamsListForSeason($season_id) {
		$mySQL = new Database\MySQLFunctions();
		$dbcon = $mySQL->connectMySQLDB();
		
		$teamsList = $mySQL->getTeamsListForSeason($dbcon, $season_id);
		
		return $teamsList;
	}

    function storeTestingParameters($season_id, $draw_coefficient, $home_booster, $lp_weighting = null, $form_weighting = null) {
        $mySQL = new Database\MySQLFunctions();
        $dbcon = $mySQL->connectMySQLDB();

        $testingParameters = [
            'season_id' => $season_id,
            'draw_coefficient' => $draw_coefficient,
            'home_booster' => $home_booster,
            'lp_weighting' => $lp_weighting,
            'form_weighting' => $form_weighting
        ];

        $testingID = $mySQL->storeTestingParameters($dbcon, $testingParameters);
        return $testingID;
    }

    function storePredictions($testingID, $results) {
        $mySQL = new Database\MySQLFunctions();
        $dbcon = $mySQL->connectMySQLDB();

        $mySQL->storePredictions($dbcon, $testingID, $results);
    }
}
	
?>