<?php

namespace Database;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

Class Scraper {

    function crawlUrl($url) {
        //	init CURL
        $curl_handle = curl_init($url);
        $user_agent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.2 (KHTML, like Gecko) Chrome/22.0.1216.0 Safari/537.2';
        //	set opts
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, $user_agent);
        //	Get page content
        $curl_scraped_page = curl_exec($curl_handle);
        curl_close($curl_handle);

        //	use DOM to manipulate HTML
        $dom = new DOMDocument();
        //	@ suppresses errors from invalid HTML
        @$dom->loadHTML($curl_scraped_page);

        $xpath = new DOMXPath($dom);
        //	get tr's in results table
        $rows = $xpath->query(".//tbody");
        foreach($rows as $index => $row){
            echo ($index + 1) . ') ' . $row->textContent . '<br />';
        }
//        print_r($rows);

        $games = array();

        if (!is_null($rows)) {
            //	iterate through tr's
            foreach ($rows as $row) {
                //	team names
                $teams = $xpath->query("td[1]/a", $row)->item(0)->nodeValue;
                list($team_1, $team_2) = explode(" - ", $teams);
                //	score
                $score = $xpath->query("td[2]/a", $row)->item(0)->nodeValue;
                list($goals, $bullshit) = explode(" ", $score);
                list($goals_1, $goals_2) = explode(":", $goals);
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

    public function getResults($url)
    {
        $client = new Client();

        $crawler = $client->request('GET', $url);

//        $nodeValues = $crawler->filter('tr > td')->each(function (Crawler $node) {
//            return $node->text();
//        });
//        print_r($nodeValues);
        $games = [];
        $trElements = $crawler->filter('tr');
        $i = 0;
        foreach ($trElements as $trElement) {
            $i++;
            $crawler = new Crawler($trElement);
            $tdElements = $crawler->filter('td');
            foreach ($tdElements as $i => $node) {
                echo($node->nodeValue."\n");
                switch($i) {
                    case 1:
                        $crawler = new Crawler($node);
                        $teams = $crawler->filter('span');
                        $hTeam = $teams->eq(0)->text();
                        $aTeam = $teams->eq(1)->text();
                        break;
                    case 2:
                        list($hGoals, $aGoals) = explode(":", $node->nodeValue);
                        break;
                    case 6:
                        list($day, $month, $year) = explode(".", $node->nodeValue);
                        $date = $year . '-' . $month . '-' . $day;
                        break;
                    default:
                        continue;
                }
            }
//            $games[] = [
//                'hteam'		=> $hTeam,
//                'ateam'		=> $aTeam,
//                'goals_ht'	=> $hGoals,
//                'goals_at'	=> $aGoals,
//                'game_date'	=> $date
//            ];
//            if ($i > 4) {
//                break;
//            }
        }
        print_r($games);
    }
}

?>