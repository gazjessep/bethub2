<?php

include_once('test.php');

echo('Starting test...'."\r\n");

$class = new Testing\Model();

foreach (range(1,5,1) as $season_id) {
    echo('Starting testing for season '.(string)$season_id."\r\n");
    $class->testIndex($season_id);
    echo('Completed testing for season '.(string)$season_id."\r\n");
}

