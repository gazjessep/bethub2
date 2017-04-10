<?php

namespace Database;

use Exception;

class Config
{
    const DB_PROD = 'production';
    const DB_LOCAL = 'local';

    private static $config = [
        'scott' => [
            'production' => [
                'database'	=> [
                    'ip'		=> '125.236.205.176',
                    'dbname'	=> 'bethub',
                    'port'      => '3306',
                    'username'	=> 'scott',
                    'password'	=> 'SC0TTbethub'
                ],
                'crawler' => [
                    'base_url' => 'http://www.betexplorer.com/soccer/'
                ]
            ],
            'local' => [
                'database'	=> [
                    'ip'		=> '127.0.0.1',
                    'port'      => '3308',
                    'dbname'	=> 'bethub',
                    'username'	=> 'scott',
                    'password'	=> 'scottBETHUB'
                ],
                'crawler' => [
                    'base_url' => 'http://www.betexplorer.com/soccer/',
                    'end_url' => [
                        'results' => '/results/',
                        'fixtures' => '/fixtures/'
                    ]
                ]
            ]
        ],
        'gaz' => [
        ]
    ];

    public static function getConfig($env, $user)
    {
        if (isset(Config::$config[$user])) {
            $config = Config::$config[$user][$env];
        } else {
            throw new Exception('User not found!');
        }
        return $config;
    }
}

?>