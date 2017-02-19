<?php
namespace Testing;

use Database;
use Logic;

include_once('../database/mysql_functions.php');
include_once('../logic/prediction.php');

class Model
{
    function testIndex($season_id, $_testingParameters) {
        $lp_weighting = 1.00;

        $mySQL = new Database\MySQLFunctions();
        $dbcon = $mySQL->connectMySQLDB();

        // Get the seasons results & fixtures
        $fixtures = $this->getSeason($season_id, $dbcon);

        // Loop through our draw/win cutoff
        foreach (range($_testingParameters['draw_coefficient']['min'],
            $_testingParameters['draw_coefficient']['max'],
            $_testingParameters['draw_coefficient']['increment']) as $draw_coefficient) {
            foreach (range($_testingParameters['home_booster']['min'],
                $_testingParameters['home_booster']['max'],
                $_testingParameters['home_booster']['increment']) as $home_booster) {
                foreach (range($_testingParameters['form_weighting']['min'],
                    $_testingParameters['form_weighting']['max'],
                    $_testingParameters['form_weighting']['increment']) as $form_weighting) {
                    $testingParameters = [
                        'draw_coefficient' => $draw_coefficient,
                        'home_booster' => $home_booster,
                        'lp_weighting' => $lp_weighting,
                        'form_weighting' => $form_weighting
                    ];
                    $testingID = $this->storeTestingParameters($dbcon, $season_id, $testingParameters);
                    if ($testingID == 'TestAlreadyRun') {
                        continue;
                    }
                    $results = $this->testPredictions($dbcon, $season_id, $fixtures, $testingParameters);
                    $this->storePredictions($testingID, $results, $dbcon);
                }
            }
        }
    }
	function testPredictions($dbcon, $season_id, $fixtures, $testingParameters) {
	    echo('Testing Predictions for...'."\r\n");
        print_r($testingParameters);

        $prediction = new Logic\PredictGames();

		$teamsArray = [];
		$teamsList = $this->getTeamsListForSeason($season_id, $dbcon);

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
			$predictedResult = $prediction->determineWinner($dbcon, $fixture, $season_id, $testingParameters);
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
	
	function getSeason($season_id, $dbcon) {
		$mySQL = new Database\MySQLFunctions();
		
		$fixtures = $mySQL->getSeasonFixtures($dbcon, $season_id);
		
		return $fixtures;
	}

	function getTeamsListForSeason($season_id, $dbcon) {
		$mySQL = new Database\MySQLFunctions();
		
		$teamsList = $mySQL->getTeamsListForSeason($dbcon, $season_id);
		
		return $teamsList;
	}

    function storeTestingParameters($dbcon, $season_id, $testingParameters) {
        $mySQL = new Database\MySQLFunctions();

        $testingParameters['season_id'] = $season_id;
        // First, check if we have already run this test on this season
        $checkParameters = $mySQL->checkTestingParameters($dbcon, $testingParameters);
        if (count($checkParameters) != 0) {
            echo('Already tested season '.$season_id.' with these parameters'."\r\n");
            return 'TestAlreadyRun';
        }

        $testingID = $mySQL->storeTestingParameters($dbcon, $testingParameters);
        return $testingID;
    }

    function storePredictions($testingID, $results, $dbcon) {
        $mySQL = new Database\MySQLFunctions();

        $mySQL->storePredictions($dbcon, $testingID, $results);
    }

    function createDataset($season_id) {
        $mySQL = new Database\MySQLFunctions();
        $prediction = new Logic\PredictGames();
        $dbcon = $mySQL->connectMySQLDB();

        $fixtures = $this->getSeason($season_id, $dbcon);

        $leaguePosition = $prediction->checkLeaguePosition($dbcon, $mySQL, $season_id, $fixtures[0]['game_date']);
        $form = $prediction->checkForm($dbcon, $mySQL, $season_id, $fixtures[0]['game_date']);
        $date = $fixtures[0]['game_date'];
        $count = 0;

        foreach ($fixtures as $fixture) {
            $count++;
            if ($count > 20) {
                if ($date == $fixture['game_date']) {
                    $dataset[] = [
                        'home_team_id' => $fixture['home_team_id'],
                        'home_team_lp' => $leaguePosition[$fixture['home_team_id']],
                        'home_team_form' => $form[$fixture['home_team_id']],
                        'away_team_id' => $fixture['away_team_id'],
                        'away_team_lp' => $leaguePosition[$fixture['away_team_id']],
                        'away_team_form' => $form[$fixture['away_team_id']],
                        'home_points' => $fixture['game_points']
                    ];
                } else {
                    $date = $fixture['game_date'];
                    $leaguePosition = $prediction->checkLeaguePosition($dbcon, $mySQL, $season_id, $date);
                    $form = $prediction->checkForm($dbcon, $mySQL, $season_id, $date);
                    $dataset[] = [
                        'home_team_id' => $fixture['home_team_id'],
                        'home_team_lp' => $leaguePosition[$fixture['home_team_id']],
                        'home_team_form' => $form[$fixture['home_team_id']],
                        'away_team_id' => $fixture['away_team_id'],
                        'away_team_lp' => $leaguePosition[$fixture['away_team_id']],
                        'away_team_form' => $form[$fixture['away_team_id']],
                        'home_points' => $fixture['game_points']
                    ];
                }
            }
        }

        if (isset($dataset)) {
            $this->storeasCSV($season_id, $dataset);
        }
    }
    function storeasCSV($season_id, $dataset = []) {
        // Use timestamp in the file handle - to ensure each test set is unique
        $time = time();
        $handle = fopen('./output/season_'.$season_id.'_dataset_'.$time.'.csv', 'w');

        // Add the header of the CSV file
        fputcsv($handle, array('home_team_id', 'home_team_lp', 'home_team_form', 'away_team_id', 'away_team_lp', 'away_team_form', 'home_points'),',');
        // Add the data queried from database
        foreach($dataset as $row) {
            fputcsv(
                $handle, // The file pointer
                array($row['home_team_id'], $row['home_team_lp'], $row['home_team_form'], $row['away_team_id'], $row['away_team_lp'], $row['away_team_form'], $row['home_points']), // The fields
                ',' // The delimiter
            );
        }
        fclose($handle);
    }
}
	
?>