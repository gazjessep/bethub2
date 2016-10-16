<?php

namespace Database;

Class Index
{
//include_once('mysql_functions.php');
//include_once('crawler.php');

    function addSeason ($league_name = 'english_premier_league', $league_country = 'england', $league_url = 'england/premier-league-', $year = 2013)
    {
        $mySQL = new MySQLFunctions();
        try {
            $mySQLcon = $mySQL->connectMySQLDB();
        } catch (\Exception $e) {
            throw new $e($e->getMessage());
        }


        // currently this section is hardcoded
//        $league_name = 'english_premier_league';
//        $league_country = 'england';
//        $league_url = 'england/premier-league-';
//        $year = 2013;
        //

        if ($league_id = $mySQL->leagueExists($mySQLcon, $league_name, $league_country)) {
            echo('Warning: League already exists, already scraped!');
            exit("\r\n");
        } else {
            $league_id = $mySQL->insertLeague($mySQLcon, $league_name, $league_country, $league_url);
        }

        if ($season_id = $mySQL->seasonExists($mySQLcon, $league_id, $year)) {
            echo('Warning: Season already exists, already scraped!');
            exit("\r\n");
        } else {
            $season_id = $mySQL->insertSeason($mySQLcon, $year, $league_id);
        }

        $buildURL = 'http://www.betexplorer.com/soccer/' . $league_url . strval($year) . '-' . strval($year + 1) . '/results/';

        $crawler = new Crawler();
        $games = $crawler->crawlUrl($buildURL);
        echo(count($games) . ' games' . PHP_EOL);

        foreach ($games as $game) {

            // check data for missing fields
            if (empty($game['hteam']) || empty($game['ateam']) || is_null($game['goals_ht']) || is_null($game['goals_at']) || empty($game['game_date'])) {
                $missingData[] = $game;
                continue;
            }

            // set points for each team
            if ($game['goals_ht'] > $game['goals_at']) {
                $homepoints = 3;
                $awaypoints = 0;
            } elseif ($game['goals_at'] > $game['goals_ht']) {
                $homepoints = 0;
                $awaypoints = 3;
            } else {
                $homepoints = 1;
                $awaypoints = 1;
            }

            $home_game_data = [
                'game_date' => $game['game_date'],
                'homepoints' => $homepoints,
                'goalsfor' => $game['goals_ht'],
                'goalsagainst' => $game['goals_at'],
                'goaldifference' => $game['goals_ht'] - $game['goals_at']
            ];

            $away_game_data = [
                'game_date' => $game['game_date'],
                'homepoints' => $awaypoints,
                'goalsfor' => $game['goals_at'],
                'goalsagainst' => $game['goals_ht'],
                'goaldifference' => $game['goals_at'] - $game['goals_ht']
            ];

            $hteam_id = $mySQL->teamExists($mySQLcon, strtolower($game['hteam']), $league_country);
            $ateam_id = $mySQL->teamExists($mySQLcon, strtolower($game['ateam']), $league_country);

            // check both team exist, or one exists, insert fixture and results
            if ($hteam_id !== false && $ateam_id !== false) {
                $fixture_id = $mySQL->insertFixture($mySQLcon, $game['game_date'], $season_id, $hteam_id, $ateam_id);
                $mySQL->insertHomeGame($mySQLcon, $home_game_data, $season_id, $hteam_id, $fixture_id);
                $mySQL->insertAwayGame($mySQLcon, $away_game_data, $season_id, $ateam_id, $fixture_id);

            } elseif ($hteam_id !== false || $ateam_id !== false) {
                if ($hteam_id === false) {
                    $hteam_id = $mySQL->insertTeam($mySQLcon, strtolower($game['hteam']), $league_country, $league_id);
                    $fixture_id = $mySQL->insertFixture($mySQLcon, $game['game_date'], $season_id, $hteam_id, $ateam_id);
                    $mySQL->insertHomeGame($mySQLcon, $home_game_data, $season_id, $hteam_id, $fixture_id);
                    $mySQL->insertAwayGame($mySQLcon, $away_game_data, $season_id, $ateam_id, $fixture_id);

                } else {
                    $ateam_id = $mySQL->insertTeam($mySQLcon, strtolower($game['ateam']), $league_country, $league_id);
                    $fixture_id = $mySQL->insertFixture($mySQLcon, $game['game_date'], $season_id, $hteam_id, $ateam_id);
                    $mySQL->insertHomeGame($mySQLcon, $home_game_data, $season_id, $hteam_id, $fixture_id);
                    $mySQL->insertAwayGame($mySQLcon, $away_game_data, $season_id, $ateam_id, $fixture_id);

                }
            } else {
                $hteam_id = $mySQL->insertTeam($mySQLcon, strtolower($game['hteam']), $league_country, $league_id);
                $ateam_id = $mySQL->insertTeam($mySQLcon, strtolower($game['ateam']), $league_country, $league_id);
                $fixture_id = $mySQL->insertFixture($mySQLcon, $game['game_date'], $season_id, $hteam_id, $ateam_id);
                $mySQL->insertHomeGame($mySQLcon, $home_game_data, $season_id, $hteam_id, $fixture_id);
                $mySQL->insertAwayGame($mySQLcon, $away_game_data, $season_id, $ateam_id, $fixture_id);

            }
        }
        // print out any missing data
        if (isset($missingData)) {
            echo('Data in array below is missing data, please check!');
            echo("\r\n");
            print_r($missingData);
        }
    }
}

?>