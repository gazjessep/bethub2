<?php
namespace Testing;
use Database;
class Test 
{
	function testPredictions () {
		$season_id = '6';
		$fixtures = $this->getSeason($season_id);
		
		
	}
	
	function getSeason ($season_id) {
		$mySQL = new Database->MySQLFunctions();
		$dbcon = $mySQL->connectMySQLDB();
		
		$fixtures = $mySQL->getSeasonFixtures($dbcon, $season_id);
		
		return $fixtures;
	}
	
	
}
	
?>