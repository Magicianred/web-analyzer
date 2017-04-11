<?php

require_once './Analyzer.php';
require_once './Browser.php';

use web\analyzer\Analyzer;
use web\analyzer\Browser;

//exec('bash ./req-url.sh');

$filePath = './url-lists.txt';
$rootPath = '/home/lab223/web-curl/';
$firefoxPath = '/home/lab223/firefox/firefox';
if(file_exists($filePath)) {
    $webFilePath = [
        'root-path' => $rootPath,
        'paths' => [],
    ];
    $handler = fopen($filePath, 'r');
    $urlList = [];
    $lists = [];
    while(!feof($handler)) {
        $str = trim(fgets($handler, 4096));
        if(stristr($str, '#') !== false) {
            continue;
        }
        if(stristr($str, '(') !== false) {
            $strArr = explode('(', $str);
            @mkdir($rootPath.$strArr[0]);
            $lists[] = $rootPath.$strArr[0];
            $url = str_replace([')', ' '], '', $strArr[1]);
            $urlList[] = $url;
        }
    }
    fclose($handler);
    $index = 0;
    $str = file_get_contents('./marcos.template');

    foreach($urlList as $reqUrl) {
        $str .= 'URL GOTO='.$reqUrl.PHP_EOL;
        $str .= 'SET !TIMEOUT_TAG 120'.PHP_EOL;
        $str .= 'WAIT SECONDS=20'.PHP_EOL;
        $str .= 'SAVEAS TYPE=HTM FOLDER='.$lists[$index].'  FILE=index.html'.PHP_EOL;
        $webFilePath['paths'][$index] = $lists[$index];
        $index++;
    }
    $str .= 'TAB CLOSE';
    file_put_contents('./marcos.iim', $str);
    //system($firefoxPath.' "imacros://run/?m=marcos.iim"');

    //compress image size
    //$analyer = new Analyzer($webFilePath);
    //$analyer->analyze('DOM');

    //check CSS2 or CSS3
    //$analyer = new Analyzer($webFilePath);
    //$analyer->cssVersion();

    //evaluate time before running command: php -S localhost:8000 -t /path/to/web-curl
    $browser = new Browser($webFilePath, 'firefox', $rootPath, $firefoxPath);
    $browser->eveluateTime();

} else {
    echo 'The '.$filePath.' is not existed...';
}
