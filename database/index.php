<?php

namespace Database;

use Exception;

Class Index
{
    const DB_PROD = 'production';
    const DB_LOCAL = 'local';

    private $config = [];

    public function __construct($config)
    {
        // Set config
        $this->config = $config;
    }

    function addSeason ($league_name, $league_country, $league_url, $year)
    {
        try {
            $mySQL = new MySQLFunctions($this->config);
        } catch (Exception $e) {
            throw $e;
        }

        if ($league_id = $mySQL->leagueExists($league_name, $league_country)) {
            echo('League already exists...Continuing...'."\r\n");
        } else {
            $league_id = $mySQL->insertLeague($league_name, $league_country, $league_url);
        }

        if ($season_id = $mySQL->seasonExists($league_id, $year)) {
            echo('Warning: Season already exists, already scraped!'. "\r\n");
            return 'Season_exists';
        } else {
            $season_id = $mySQL->insertSeason($year, $league_id);
        }

        $buildURL = $this->config['crawler']['base_url'] . $league_url . strval($year) . '-' . strval($year + 1) . $this->config['crawler']['end_url']['results'];

        echo($buildURL."\n");
        $crawler = new Scraper();
        $games = $crawler->getResults($buildURL);

        // If no games found, remove season and return
        if (count($games) == 0) {
            echo('No games found - crawler may not be working'."\n");
            $mySQL->deleteSeason($season_id);
            return 'Done!';
        }

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

            $hteam_id = $mySQL->teamExists(strtolower($game['hteam']), $league_country);
            $ateam_id = $mySQL->teamExists(strtolower($game['ateam']), $league_country);

            // check both team exist, or one exists, insert fixture and results
            if ($hteam_id !== false && $ateam_id !== false) {
                $fixture_id = $mySQL->insertFixture($game['game_date'], $season_id, $hteam_id, $ateam_id);
                $mySQL->insertHomeGame($home_game_data, $season_id, $hteam_id, $fixture_id);
                $mySQL->insertAwayGame($away_game_data, $season_id, $ateam_id, $fixture_id);

            } elseif ($hteam_id !== false || $ateam_id !== false) {
                if ($hteam_id === false) {
                    $hteam_id = $mySQL->insertTeam(strtolower($game['hteam']), $league_country, $league_id);
                    $fixture_id = $mySQL->insertFixture($game['game_date'], $season_id, $hteam_id, $ateam_id);
                    $mySQL->insertHomeGame($home_game_data, $season_id, $hteam_id, $fixture_id);
                    $mySQL->insertAwayGame($away_game_data, $season_id, $ateam_id, $fixture_id);

                } else {
                    $ateam_id = $mySQL->insertTeam(strtolower($game['ateam']), $league_country, $league_id);
                    $fixture_id = $mySQL->insertFixture($game['game_date'], $season_id, $hteam_id, $ateam_id);
                    $mySQL->insertHomeGame($home_game_data, $season_id, $hteam_id, $fixture_id);
                    $mySQL->insertAwayGame($away_game_data, $season_id, $ateam_id, $fixture_id);

                }
            } else {
                $hteam_id = $mySQL->insertTeam(strtolower($game['hteam']), $league_country, $league_id);
                $ateam_id = $mySQL->insertTeam(strtolower($game['ateam']), $league_country, $league_id);
                $fixture_id = $mySQL->insertFixture($game['game_date'], $season_id, $hteam_id, $ateam_id);
                $mySQL->insertHomeGame($home_game_data, $season_id, $hteam_id, $fixture_id);
                $mySQL->insertAwayGame($away_game_data, $season_id, $ateam_id, $fixture_id);

            }
        }
        // print out any missing data
        if (isset($missingData)) {
            echo('Data in array below is missing data, please check!');
            echo("\r\n");
            print_r($missingData);
        }
        return 'Done!';
    }
}

?>