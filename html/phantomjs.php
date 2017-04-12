<?php

if(isset($argv[1])) {
	$phantomPath = $argv[1];
} else {
	die('missing the arguments one: the phantomjs executable path');
}

if(isset($argv[2])) {
	$times = $argv[2];
} else {
	die('missing the arguments two: times(1 to 10)');
}

if(isset($argv[3])) {
	$type = $argv[3];
} else {
	die('missing the arguments three: the name/id/none');
}

$htmlLists = glob('measure-*.html');

foreach($htmlLists as $fileName) {
    exec($phantomPath.'/phantomjs web-defet.js '.'http://localhost/web-analyzer/html/'.$fileName,
        $output, $exitCode);
    if($exitCode !== 0) {
        echo $fileName.' got some error when running PhantomJS...'.PHP_EOL;
    } else {
        echo $fileName.' result message:'.PHP_EOL;
        var_dump($output);
    }
}

echo 'Running the PhantomJS is over.'.PHP_EOL;
echo 'Combining the csv files...'.PHP_EOL;
exec('php inte_chrome_csv.php '.$times.' '.$type, $output, $code);

if($code !== 0) {
    echo 'Combining the csv files are failed...'.PHP_EOL;
}
