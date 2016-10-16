<?php
namespace Testing;

use Database;
use Logic;

class Model
{
	function testPredictions () {
	    echo('Testing Predictions...'."\r\n");
	    // these are hardcoded for now
        $draw_coefficient = '0.15';
		$season_id = '6';

		$fixtures = $this->getSeason($season_id);
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
			if (count($teamsList) > 0) {
				unset($teamsList[$fixture['home_team_id']]);
				unset($teamsList[$fixture['away_team_id']]);
				continue;
			}
			$predictedResult = $prediction->determineWinner($fixture, $draw_coefficient, $season_id);
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
        print_r($resultsArray);
	}
	
	function getSeason ($season_id) {
		$mySQL = new Database\MySQLFunctions();
		$dbcon = $mySQL->connectMySQLDB();
		
		$fixtures = $mySQL->getSeasonFixtures($dbcon, $season_id);
		
		return $fixtures;
	}

	function getTeamsListForSeason ($season_id) {
		$mySQL = new Database\MySQLFunctions();
		$dbcon = $mySQL->connectMySQLDB();
		
		$teamsList = $mySQL->getSeasonFixtures($dbcon, $season_id);
		
		return $teamsList;
	}	
	
}
	
?>