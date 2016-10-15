<?php
namespace Testing;
use Database;
use Logic;
class Test 
{
	function testPredictions () {
		$season_id = '6';
		$fixtures = $this->getSeason($season_id);
		
		$teamsArray = [];
		$teamsList = $this->getTeamsListForSeason($season_id);
		foreach ($teamsList as $team) {
			$teamsArray[$team['home_team_id']] = 'Placeholder';
		}
		
		foreach ($fixtures as $fixture) {
			if (count($teamsList > 0)) {
				unset($teamsList[$fixture['home_team_id']]);
				unset($teamsList[$fixture['away_team_id']]);
				continue;
			}
			
			$prediction = new Logic\PredictGames();
			$predictedResult = $prediction->determineWinner($fixture);
		}
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