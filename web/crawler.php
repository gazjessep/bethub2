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
		$dom = new DOMDocument();
		//	@ suppresses errors from invalid HTML
		@$dom->loadHTML($curl_scraped_page);
		//	select results table
		//	$results_table = $dom->getElementById('leagueresults_tbody');
		
		//
		$xpath = new DOMXPath($dom);
		//	get tr's in results table
		$rows = $xpath->query("//tbody[@id='leagueresults_tbody']/tr");

		$games = array();

		if (!is_null($rows)) {
			//	iterate through tr's
			foreach ($rows as $row) {
				//	var_dump( $row->nodeValue );
				//	outputs text content of whole tr, as expected

				//	team names
				//	$teams = $xpath->query("td[1]/a", $row)->nodeValue;
				$teams = $xpath->query("td[1]/a", $row);
					var_dump($teams);
					/* outputs something like
					object(DOMNodeList)#857 (1) {
					  ["length"]=>
					  int(1)
					} */
					var_dump($teams->nodeValue);	//	outputs NULL

				list($team_1, $team_2) = explode(" - ", $teams);
				//	score
				$score = $xpath->query("td[2]/a", $row)->nodeValue;
				list($goals_1, $goals_2) = explode(":", $score);
				//	date
				$date  = $xpath->query("td[6]", $row)->nodeValue;
	
	
				$game = [	
					'hteam' => $team_1,
					'ateam' => $team_2,
					'goals_ht' => $goals_1,
					'goals_at' => $goals_2,
					//	probably don't need to set timezone when this is non-null
					'game_date' => new DateTime( $date, new DateTimeZone('UTC') )	
				];
	
				
				//	var_dump($teams);

				array_push($games, $game);


			}	//	end foreach
			
	//		var_dump($games);
			
		}	//	end if

		
		var_dump($games[count($games)-1]);
	}
	
	crawlUrl("http://www.betexplorer.com/soccer/england/premier-league-2013-2014/results/");

	//	http://www.betexplorer.com/soccer/england/premier-league-2013-2014/results/
	//	/results shows ALL results, most recent first

	function getResults() {}
	
?>