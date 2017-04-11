<?php

$outAvgCsv = './avg-render-time.csv';
@unlink($outAvgCsv);
$csvFile = glob('./chrome-r-id-*.csv');

$numbers = [
    'p' => [],
    'p-nest' => [],
    'div' => [],
    'div-nest' => [],
    'span' => [],
    'span-nest' => [],
];
$times = '0,';
$lenLimit = 9;
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
            if($indexNum >= 11) {
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
        if($indexNum >= 11) {
            break;
        }
        $renTimeStr .= round($time / 3).',';
        $indexNum++;
    }
    fwrite($newHandlerCsv, substr($renTimeStr, 0, -1).PHP_EOL);
    $renTimeStr = '';
}

fclose($newHandlerCsv);
