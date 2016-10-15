<?php

namespace Logic;

use Database;

// include_once('/../database/mysql_functions.php');
class PredictGames
{
	function determineWinner ( $fixture_id, $home_team_id, $away_team_id ) {
		$home_team_id = $fixture['home_team_id'];
		$away_team_id = $fixture['away_team_id'];
		$fixture_date = $fixture['game_date'];
		
		$mySQL = new Database\MySQLFunctions();
		$dbcon = $mySQL->connectMySQLDB();
		
		$total_points_home = 
	}
	
	function predictResult( $fixture_id, $season_data ) {
		
		
	}
}




?>