<?php

$renderTime = filter_input(INPUT_POST, "render_time");
$fileName = filter_input(INPUT_POST, "file_name");

$fileNames = [
    './measure-chrome-p.csv',
    './measure-chrome-div.csv',
    './measure-chrome-span.csv',
    './measure-chrome-nest-p.csv',
    './measure-chrome-nest-div.csv',
    './measure-chrome-nest-span.csv',
];

$mobileFileNames = [
    './measure-mobile-p.csv',
    './measure-mobile-div.csv',
    './measure-mobile-span.csv',
];

if($renderTime !== null && $fileName !== null) {
    $fileNameArr = explode('-', $fileName);
    $fileNameLen = count($fileNameArr);
    if($fileNameLen === 2) {
        foreach($fileNames as $value) {
            file_put_contents($value, $renderTime.'_0,', FILE_APPEND);
        }
    } else if($fileNameLen === 3) {
        $rMultiStr = '';
        if($fileNameArr[1] === 'div') {
            if(stristr($fileNameArr[2], 'nest') !== false) {
                $fileNameArr[2] = str_replace('nest', '', $fileNameArr[2]);
                file_put_contents($fileNames[4], $renderTime.'_'.$fileNameArr[2].',', FILE_APPEND);
            } else {
                file_put_contents($fileNames[1], $renderTime.'_'.$fileNameArr[2].',', FILE_APPEND);
            }
        } else if($fileNameArr[1] === 'p') {
            if(stristr($fileNameArr[2], 'nest') !== false) {
                $fileNameArr[2] = str_replace('nest', '', $fileNameArr[2]);
                file_put_contents($fileNames[3], $renderTime.'_'.$fileNameArr[2].',', FILE_APPEND);
            } else {
                file_put_contents($fileNames[0], $renderTime.'_'.$fileNameArr[2].',', FILE_APPEND);
            }
        } else if($fileNameArr[1] === 'span') {
            if(stristr($fileNameArr[2], 'nest') !== false) {
                $fileNameArr[2] = str_replace('nest', '', $fileNameArr[2]);
                file_put_contents($fileNames[5], $renderTime.'_'.$fileNameArr[2].',', FILE_APPEND);
            } else {
                file_put_contents($fileNames[2], $renderTime.'_'.$fileNameArr[2].',', FILE_APPEND);
            }
        } else {
            echo 'The tag name is not correct...';
        }
    } else {
        echo 'The file name is not correct...';
    }
} else {
    echo 'no post data...';
}
