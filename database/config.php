<?php
namespace Database;

class Config {

    public static $mySQL_config = [
        'name'		=> 'scott',
        'database'	=> [
            'ip'		=> '125.236.205.176',
            'dbname'	=> 'bethub',
            'port'      => '3306',
            'username'	=> 'scott',
            'password'	=> 'SC0TTbethub'
        ]
    ];
    public static $mySQL_config_local = [
        'name'		=> 'scott',
        'database'	=> [
            'ip'		=> '127.0.0.1',
            'port'      => '3308',
            'dbname'	=> 'bethub',
            'username'	=> 'scott',
            'password'	=> 'scottBETHUB'
        ]
    ];
}

?>