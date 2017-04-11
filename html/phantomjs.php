<?php

$htmlLists = glob('measure-*.html');

foreach($htmlLists as $fileName) {
    exec('phantomjs web-defet.js '.'http://localhost:5000/'.$fileName,
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
exec('php inte_chrome_csv.php', $output, $code);

if($code !== 0) {
    echo 'Combining the csv files are failed...'.PHP_EOL;
}
