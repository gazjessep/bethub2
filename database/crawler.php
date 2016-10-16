<?php

namespace Database;

use DOMDocument;
use DOMXPath;

Class Crawler {

    function crawlUrl($url) {
        //	init CURL
        $curl_handle = curl_init($url);
        //	set opts
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        //	Get page content
        $curl_scraped_page = curl_exec($curl_handle);
        curl_close($curl_handle);

        //	use DOM to manipulate HTML
        $dom = new DOMDocument();
        //	@ suppresses errors from invalid HTML
        @$dom->loadHTML($curl_scraped_page);

        //
        $xpath = new DOMXPath($dom);
        //	get tr's in results table
        $rows = $xpath->query("//tbody[@id='leagueresults_tbody']/tr[not(contains(@class, 'rtitle'))]");

        $games = array();

        if (!is_null($rows)) {
            //	iterate through tr's
            foreach ($rows as $row) {
                //	team names
                $teams = $xpath->query("td[1]/a", $row)->item(0)->nodeValue;
                list($team_1, $team_2) = explode(" - ", $teams);
                //	score
                $score = $xpath->query("td[2]/a", $row)->item(0)->nodeValue;
                list($goals_1, $goals_2) = explode(":", $score);
                //	date
                $date  = $xpath->query("td[6]", $row)->item(0)->nodeValue;
                list($day, $month, $year) = explode(".", $date);
                $date = $year . '-' . $month . '-' . $day;

                $game = [
                    'hteam'		=> $team_1,
                    'ateam'		=> $team_2,
                    'goals_ht'	=> $goals_1,
                    'goals_at'	=> $goals_2,
                    //	not sure if this matter so long as they're all the same...
                    'game_date'	=> $date
                ];

                array_push($games, $game);

            }	//	end foreach

        }	//	end if
        return $games;

    }	//	end crawlUrl

    //	http://www.betexplorer.com/soccer/england/premier-league-2013-2014/results/
    //	/results shows ALL results, most recent first

    function getResults() {}
}

?>