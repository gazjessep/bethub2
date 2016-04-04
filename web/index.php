<?php

$file = fopen("./england_premier-league_2012-2013.csv","r");

while(! feof($file)) {
	 $temp = fgetcsv($file);
	 $csv[] = [
		 'Team1' => $temp[0],
		 'Team2' => $temp[1],
		 'GT1' => $temp[2],
		 'GT2' => $temp[3],
		 'Date' => new DateTime($temp[4])
	];
}

fclose($file);
$teams = [];
// $csv = $csv[0];
$count = 1;
foreach ($csv as $game) {
	if ($$game['GT1'] > $game['GT2']) {
		$teams[] = [
			$game['Team1'] => [
				'Game' => $count,
				'Points' => 3,
				'GoalDiff' => $game['GT1'] - $game['GT2']
			],
			$game['Team2'] => [
				'Game' => $count,
				'Points' => 0,
				'GoalDiff' => $game['GT2'] - $game['GT1']
			]
		];
	} elseif ($game['GT1'] < $game['GT2']) {
		$teams[] = [
			$game['Team2'] => [
				'Game' => $count,
				'Points' => 3,
				'GoalDiff' => $game['GT2'] - $game['GT1']
			],
			$game['Team1'] => [
				'Game' => $count,
				'Points' => 0,
				'GoalDiff' => $game['GT1'] - $game['GT2']
			]
		];
	} else {
		$teams[] = [
			$game['Team2'] => [
				'Game' => $count,
				'Points' => 1,
				'GoalDiff' => $game['GT2'] - $game['GT1']
			],
			$game['Team1'] => [
				'Game' => $count,
				'Points' => 1,
				'GoalDiff' => $game['GT1'] - $game['GT2']
			]
		];
	}
	$count ++;
}
foreach($teams as $team) {
		print_r($team);
}
?>