<?php
namespace Database;

class Config {

    public static $config = [
        'production' => [
            'name'		=> 'scott',
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
            'name'		=> 'scott',
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
    ];
}

?>