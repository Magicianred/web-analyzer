<?php

/*
 * Usage: php html.php [tagName]
 * tagName: id, name or empty string
 *
*/

$base = 2;
$times = [];
for($index=0;$index<=16;$index++) {
    $times[] = pow($base, $index);
}

$fileNames = [
    './measure-chrome-p.csv',
    './measure-chrome-div.csv',
    './measure-chrome-span.csv',
    './measure-chrome-nest-p.csv',
    './measure-chrome-nest-div.csv',
    './measure-chrome-nest-span.csv',
];

$htmlList = glob('./*.html');
foreach($fileNames as $value) {
    @unlink($value);
    $fileInfo = pathinfo($value);
    $tagArr = explode('-', $fileInfo['filename']);
    if(count($tagArr) === 3) {
        $tagName = $tagArr[count($tagArr)-1];
    } else {
        $tagName = $tagArr[count($tagArr)-1].'-'.$tagArr[count($tagArr)-2];
    }
    $str = $tagName.'-0,';
    foreach($times as $val) {
        $str .= $tagName.'-'.$val.',';
    }
    file_put_contents($value, substr($str, 0, -1).PHP_EOL);
}

foreach($htmlList as $value) {
    if(file_exists($value)) {
        @unlink($value);
    }
}

$btnStr = '<button onclick="clickBtn()">click</button>';
$html = '<!DOCTYPE html><html><head>
<meta http-equiv="cache-control" content="max-age=0" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />
<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
<meta http-equiv="pragma" content="no-cache" />        
<script>
    document.onreadystatechange = function() {
        if(document.readyState === "complete") {
            var renderTime = performance.timing.domComplete - performance.timing.domLoading;
            console.log(renderTime);
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if(this.readyState == 4 && this.status == 200) {
                    console.log("The csv file is generated...");
                }
            };

            xhr.open("POST", "http://localhost/gen_csv.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send("render_time=" + renderTime + "&file_name=#_THEFILENAME_#");
        }
    }
</script>
</head><body id="the-body">';

if(isset($argv[1])) {
    if($argv[1] === 'name') {
        $tags = [
            'p' => [
                '<p name="this-name">This is text','</p>'
            ],
            'div' => [
                '<div name="this-name">This is text','</div>'
            ],
            'span' => [
                '<span name="this-name">This is text','</span>'
            ],
        ];
    } else if($argv[1] === 'id') {
        $tags = [
            'p' => [
                '<p #_ID_#>This is text','</p>'
            ],
            'div' => [
                '<div #_ID_#>This is text','</div>'
            ],
            'span' => [
                '<span #_ID_#>This is text','</span>'
            ],
        ];
    }
} else {
    $tags = [
        'p' => [
            '<p>This is text','</p>'
        ],
        'div' => [
            '<div>This is text','</div>'
        ],
        'span' => [
            '<span>This is text','</span>'
        ],
    ];
}

file_put_contents('measure-0.html', str_replace('#_THEFILENAME_#', 'measure-0.csv', $html).'</body></html>');
foreach($times as $len) {
    $numberLen = $len;
    foreach($tags as $tagKey => $tagArray) {
        $tagNest = '';
        $endTagNest = '';
        $handler = fopen('measure-'.$tagKey.'-'.$len.'.html', 'w+');
        $handlerNest = fopen('measure-'.$tagKey.'-nest'.$len.'.html', 'w+');
        fputs($handler, str_replace('#_THEFILENAME_#', 'measure-'.$tagKey.'-'.$len, $html));
        fputs($handlerNest, str_replace('#_THEFILENAME_#', 'measure-'.$tagKey.'-nest'.$len, $html));
        for($index=0;$index<$numberLen;$index++) {
            if(empty($argv[1]) || $argv[1] !== 'id') {
                $tagNest .= $tagArray[0];
                $tag = $tagArray[0];
            } else {
                $tagNest .= str_replace('#_ID_#', 'tag-'.$index, $tagArray[0]);
                $tag = str_replace('#_ID_#', 'tag-'.$index, $tagArray[0]);
            }
            $endTagNest .= $tagArray[1];
            $endTag = $tagArray[1];
            fputs($handler, $tag.$endTag);
        }

        fputs($handler, '</body></html>');
        fputs($handlerNest, $tagNest.$endTagNest.'</body></html>');
        fclose($handler);
        fclose($handlerNest);
    }
}
$fileLists = [];
$fileNames = [
    './measure-div-#_NUMBER_#.html',
    './measure-div-nest#_NUMBER_#.html',
    './measure-p-#_NUMBER_#.html',
    './measure-p-nest#_NUMBER_#.html',
    './measure-span-#_NUMBER_#.html',
    './measure-span-nest#_NUMBER_#.html',
];
$timeRev = array_reverse($times);
foreach($fileNames as $value) {
    foreach($timeRev as $number) {
        $fileLists[] = str_replace('#_NUMBER_#', (string)$number, $value);
    }
}
$fileLists[] = './measure-0.html';
$fileListStr = '';

foreach($fileLists as $value) {
    $fileListStr .= 'linkList.push("http://localhost/'.$value.'");'.PHP_EOL;
}

$evalString = '<!DOCTYPE html>
<html>
    <head>
        <title>evaluate html page</title>
        <meta http-equiv="cache-control" content="max-age=0" />
        <meta http-equiv="cache-control" content="no-cache" />
        <meta http-equiv="expires" content="0" />
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <meta http-equiv="pragma" content="no-cache" />
        <script>
            var linkList = [];
            '.$fileListStr.'
            var index = 0;
            document.onreadystatechange = function () {
                if (document.readyState === "complete") {
                    var ifr = document.getElementById("this-iframe");
                    ifr.src = linkList[index];
                    ifr.onload = function() {
                        index += 1;
                        if(index === linkList.length) {
                            ifr.onload = function() {
                                window.frames[0].stop();
                                return;
                            }
                            return;
                        }
                        ifr.src = linkList[index];
                    };
                }
            };
        </script>
    </head>
    <body>
        <iframe id="this-iframe" src=""></iframe>
    </body>
</html>';
file_put_contents('./evaluate.html', $evalString);
