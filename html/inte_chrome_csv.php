<?php

$filePath = './chrome-r.csv';

if(file_exists($filePath)) {
    @unlink($filePath);
}

$fileNames = [
    './measure-chrome-p.csv',
    './measure-chrome-nest-p.csv',
    './measure-chrome-div.csv',
    './measure-chrome-nest-div.csv',
    './measure-chrome-span.csv',
    './measure-chrome-nest-span.csv',
];

$newFileNames = [
    './chrome-p.csv',
    './chrome-nest-p.csv',
    './chrome-div.csv',
    './chrome-nest-div.csv',
    './chrome-span.csv',
    './chrome-nest-span.csv',
];

$index = 0;
$inteCsv = fopen($filePath, 'w+');
$timesIndex = 16;
$times = range(0, $timesIndex);
$str = '0,';
foreach($times as $value) {
    $str .= (string)(pow(2, $value)).',';
}
fwrite($inteCsv, substr($str, 0, -1).PHP_EOL);

foreach($fileNames as $value) {
    @unlink($newFileNames[$index]);
    $handlerCsv = fopen($value, 'r');
    $newHandlerCsv = fopen($newFileNames[$index], 'w+');
    while(($data = fgetcsv($handlerCsv, 1000, ",")) !== false) {
        $num = count($data);
        if($num === 0) {
            break;
        }
        $str = '';
        $numbers = [];
        foreach($data as $value) {
            if($value === '') {
                break;
            }
            if(is_numeric($value) === false && (stristr($value, 'div') !== false
                    || stristr($value, 'span') !== false || stristr($value, 'p') !== false)) {
                $valueArr = explode('-', $value);
                if(count($valueArr) === 2) {
                    $str .= $valueArr[1].',';
                } else {
                    $str .= $valueArr[2].',';
                }
            } else {
                $valueArr = explode('_', $value);
                $renderTime = $valueArr[0];
                $countTags = $valueArr[1];
                if((int)$countTags === 0) {
                    $numbers[0] = $renderTime;
                } else {
                    $numbers[(int)(log($countTags, 2))+1] = $renderTime;
                }
            }
        }
        if($str !== '') {
            fwrite($newHandlerCsv, substr($str, 0, -1).PHP_EOL);
        } else {
            $str = '';
            $i = 0;
            for(;$i<count($numbers);$i++) {
                $str .= $numbers[$i].',';
            }
            fwrite($newHandlerCsv, substr($str, 0, -1).PHP_EOL);
            fwrite($inteCsv, substr($str, 0, -1).PHP_EOL);
        }
    }
    fclose($handlerCsv);
    fclose($newHandlerCsv);
    $index += 1;
}

fclose($inteCsv);
