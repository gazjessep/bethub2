<?php

	function crawlUrl($url) {
		//	init CURL
		$curl_handle = curl_init($url);
		//	set opts
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		//	Get page content
		$curl_scraped_page = curl_exec($curl_handle);
		curl_close($curl_handle);

	//	echo $curl_scraped_page;

		//	use DOM to manipulate HTML
		$dom = new DOMDocument;
		//	@ suppresses errors from invalid HTML
		@$dom->loadHTML($curl_scraped_page);
		//	results table
		//	$results_table = $dom->getElementById('leagueresults_tbody');
		
		//
		$xpath = new DOMXPath($dom);
		//
		$rows = $xpath->query("//tbody[@id='leagueresults_tbody']/tr");

	//	echo count( $rows );
	//	var_dump($rows);
	
		$games = array();
		
		foreach ($rows as $row) {


			//	team names
			$teams = $xpath->query("td[1]/a", $row)->nodeValue;	//->nodeValue;
			list($team_1, $team_2) = explode(" - ", $teams);
			//	score
			$score = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' result ')]", $row)->plaintext;
			list($goals_1, $goals_2) = explode(":", $score);
			//	date
			$date  = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' date ')]", $row)->plaintext;

			$game = [	
				'hteam' => $team_1,
				'ateam' => $team_2,
				'goals_ht' => $goals_1,
				'goals_at' => $goals_2,
				//	probably don't need to set timezone when this is non-null
				'game_date' => new DateTime( $date, new DateTimeZone('UTC') )	
			];

			
			var_dump($teams);

			array_push($games, $game);


		//	var_dump($row);

        }	//	foreach
		
		var_dump($games[count($games)-1]);
	}
	
	crawlUrl("http://www.betexplorer.com/soccer/england/premier-league-2013-2014/results/");

	//	http://www.betexplorer.com/soccer/england/premier-league-2013-2014/results
	//	/results shows ALL results, most recent first

	function getResults() {}
	
?>