<?php

namespace Database;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

Class Scraper
{
    public function getResults($url)
    {
        $client = new Client();

        $crawler = $client->request('GET', $url);

        $games = [];
        $trElements = $crawler->filter('tr');
        foreach ($trElements as $trElement) {
            // Each loop unset variables (in case of bad data)
            unset($hTeam, $hGoals, $aTeam, $aGoals, $date);
            $crawler = new Crawler($trElement);
            $tdElements = $crawler->filter('td');
            foreach ($tdElements as $i => $node) {
//                echo('I='.$i.' Node='.$node->nodeValue."\n");
                switch($i) {
                    case 0:
                        if ($node->nodeValue != '') {
                            $crawler = new Crawler($node);
                            $teams = $crawler->filter('span');
                            $hTeam = $teams->eq(0)->text();
                            $aTeam = $teams->eq(1)->text();
                        }
                        break;
                    case 1:
                        if ($node->nodeValue != '') {
                            list($hGoals, $aGoals) = explode(":", $node->nodeValue);
                        }
                        break;
                    case 5:
                        if ($node->nodeValue != '') {
                            list($day, $month, $year) = explode(".", $node->nodeValue);
                            $date = $year . '-' . $month . '-' . $day;
                        }
                        break;
                    default:
                        continue;
                }
            }

            // Make sure the row had valid data, otherwise we will skip it
            if (isset($hTeam) && isset($aTeam) && isset($hGoals) && isset($aGoals) && isset($date)) {
                $games[] = [
                    'hteam'		=> $hTeam,
                    'ateam'		=> $aTeam,
                    'goals_ht'	=> $hGoals,
                    'goals_at'	=> $aGoals,
                    'game_date'	=> $date
                ];
            } else {
                echo('Row does not have valid game result data: data shown below'."\n");
                echo($trElement->nodeValue."\n");
            }
        }
        // Return array of games
        return $games;
    }
}

?>