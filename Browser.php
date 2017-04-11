<?php

namespace web\analyzer;
require_once 'vendor/autoload.php';
require_once 'database/Database.php';

use Symfony\Component\DomCrawler\Crawler;

class Browser {
    private $filePath = '';
    private $timing = 0;
    private $userAgent = '';
    private $firefoxPath = '';

    public function __construct($filePath, $userAgent, $rootPath, $firefoxPath) {
        $this->filePath = $filePath;
        $this->userAgent = $userAgent;
        $this->rootPath = $rootPath;
        $this->firefoxPath = $firefoxPath;
    }

    public function eveluateTime() {
        switch($this->userAgent) {
            case 'firefox':
                $this->firefox();
                break;
            case 'chrome':
                $this->chrome();
                break;
            default:
                $this->phantomJs();
                break;
        }
    }

    private function firefox() {
        $marcoPath = './marcos.iim';
        @unlink($marcoPath);
        $contents = file_get_contents('./browser.template');
        foreach($this->filePath['paths'] as $path) {
            $reqUrl = str_replace($this->rootPath, '', $path);
            $marcos = $contents.PHP_EOL;
            $marcos .= 'STOPWATCH ID=Firstpage'.PHP_EOL;
            $marcos .= 'URL GOTO='.'http://localhost:8000/'.$reqUrl.'/index.html'.PHP_EOL;
            $marcos .= 'STOPWATCH ID=Firstpage'.PHP_EOL;
            $marcos .= PHP_EOL;
            $marcos .= 'SAVEAS TYPE=HTM FOLDER='.$path.'  FILE=index-timing.html'.PHP_EOL;
            file_put_contents($marcoPath, $marcos, FILE_APPEND);
        }

        system($this->firefoxPath.' "imacros://run/?m=marcos.iim"');
    }

    private function chrome() {
        
    }

    private function phantomJs() {
        
    }

    private function generateMarocs() {

    }
}
