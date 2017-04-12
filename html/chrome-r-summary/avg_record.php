<?php

$outAvgCsv = '';

$csvFilePath = './chrome-r-*.csv';
if(isset($argv[1])) {
	if($argv[1] === 'id') {
		$csvFilePath = './chrome-r-id-*.csv';
	} else if($argv[1] === 'name') {
		$csvFilePath = './chrome-r-name-*.csv';
	} else {
		$outAvgCsv = './avg-render-time.csv';
	}
} else {
	die('missing the argument one: id/name/none');
}

if(isset($argv[2]) && is_numeric($argv[2])) {
	if($argv[1] !== 'none') {
		$outAvgCsv = './avg-render-time-'.$argv[1].'-'.$argv[2];
	} else {
		$outAvgCsv = './avg-render-time-'.$argv[2];
	}
} else {
	die('missing the argument two: 9 or 16');
}

if(isset($argv[3]) && is_numeric($argv[3])) {
	$runningTime = $argv[3];
} else {
	die('missing the argument three is about the running times: 3/10');
}

if(isset($argv[4]) && is_string($argv[4])) {
	$browserName = $argv[4];
	if($browserName !== 'chrome') {
		$outAvgCsv .= '-'.$browserName.'.csv';
	} else {
		$outAvgCsv .= '.csv';
	}
} else {
	die('missing the argument four is about the web browser name: firefox/chrome/edge');
}

@unlink($outAvgCsv);
$csvFile = glob($csvFilePath);

$numbers = [
    'p' => [],
    'p-nest' => [],
    'div' => [],
    'div-nest' => [],
    'span' => [],
    'span-nest' => [],
];
$lenLimit = $argv[2];
$times = '0,';
$timeArr = range(0, $lenLimit);

foreach($timeArr as $value) {
    $times .= pow(2, $value).',';
}

foreach($numbers as $key => $eleArr) {
    for($index=0;$index<=17;$index++) {
        $numbers[$key][$index] = 0;
    }
}

foreach($csvFile as $fileName) {
    $handlerCsv = fopen($fileName, 'r');
    $index = 0;
    $indexKey = 0;
    $tagArr = array_keys($numbers);
    while(($data = fgetcsv($handlerCsv, 1000, ",")) !== false) {
        $num = count($data);
        if($index == 0) {
            $index++;
            continue;
        }
        if($num === 0) {
            break;
        }
        $indexNum = 0;
        foreach($data as $value) {
            if($indexNum >= 11 && $lenLimit == 9) {
                break;
            }
            $numbers[$tagArr[$indexKey]][$indexNum] += (int)$value;
            $indexNum += 1;
        }
        $index++;
        $indexKey++;
    }
    fclose($handlerCsv);
}
$newHandlerCsv = fopen($outAvgCsv, 'w+');
fwrite($newHandlerCsv, substr($times, 0, -1).PHP_EOL);

$renTimeStr = '';
foreach($numbers as $key => $renderTimeArr) {
    $indexNum = 0;
    foreach($numbers[$key] as $time) {
        if($indexNum >= 11 && $lenLimit == 9) {
            break;
        }
        $renTimeStr .= round($time / $runningTime).',';
        $indexNum++;
    }
    fwrite($newHandlerCsv, substr($renTimeStr, 0, -1).PHP_EOL);
    $renTimeStr = '';
}

fclose($newHandlerCsv);
