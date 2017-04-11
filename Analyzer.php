<?php
namespace web\analyzer;
require_once 'vendor/autoload.php';
require_once 'database/Database.php';

use Symfony\Component\DomCrawler\Crawler;

class Analyzer {
    private $filePath = [];
    private $fileUrl = '';
    private $result = [];
    private $db = null;

    public function __construct(array $filePath) {
        $this->db = $this->connectDb();
        if(is_array($filePath)) {
            $this->filePath = $filePath;
        } else {
            echo 'The $filePath variable type is array.';
        }     
    }

    public function analyze($type) {
        if(is_string($type)) {
            switch($type) {
                case 'image':
                    $this->anaImageSize();
                    break;
                case 'DOM':
                    $this->anaDomHierarchy();
                    break;
            }
        } else {
            echo 'variable $type is not a valid string.';
        }
    }

    public function cssVersion() {
        foreach($this->filePath['paths'] as $filePath) {
            $this->result = [];
            $contents = file_get_contents($filePath.'/index.html');
            $crawler = new Crawler($contents);
            $crawler->filter('link')->each(function(Crawler $node) {
                $stylesheet = $node->attr('rel');
                if($stylesheet === 'stylesheet') {
                    if(!empty($node->attr('href'))) {
                        $this->result[] = $node->attr('href');
                    }
                }
            });
            foreach($this->result as $cssPath) {
                $this->storeCss($cssPath, $filePath.'/index.html');
            }
        }
        $this->db = null;
    }

    public function anaJs() {
        foreach($this->filePath['paths'] as $filePath) {
            $this->result = [];
            $contents = file_get_contents($filePath.'/index.html');
            $crawler = new Crawler($contents);
            $crawler->filter('script')->each(function(Crawler $node) {
                $script = $node->attr('src');
                $this->result[] = $script;
            });
            foreach($this->result as $jsPath) {
                $this->storeJs($jsPath, $filePath.'/index.html');
            }
        }
    }

    private function storeJs($jsPath, $filePath) {
        $fileName = $this->downloadFile($jsPath, $filePath, 'js');
        if($fileName === null || file_exists($fileName) === false) {
            return null;
        }
        $fileSize = filesize($fileName);
        $numberFunctions = 0;
        $sql = "INSERT IGNORE INTO `js-file`(`number-functions`, `file-size`, `original`) VALUES(:functions, :size, :original)";
        $stmt = $conn->prepare($sql, [
            \PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY
        ]);
        $stmt->execute([
            ':functions' => $fileName,
            ':size' => $fileSize,
            ':original' => $jsPath
        ]);

    }

    private function storeCss($cssPath, $filePath) {
        $checkCss3 = 2;
        $rule = $this->cssRule();
        $features = [];
        $filePath = str_replace('/index.html', '', $filePath);
        $fileName = $this->downloadFile($cssPath, $filePath, 'css');
        if($fileName === null || file_exists($fileName) === false){
            return null;
        }
        $contents = file_get_contents($fileName);
        foreach($rule as $key => $value) {
            foreach($value as $feature) {
                if(stristr($contents, $feature)) {
                    $features[] = $feature;
                    $checkCss3 = 3;
                }
            }
        }
        if($checkCss3 === 2) {
            $features[] = 'non-css3-feature';
        }
        $conn = $this->db;
        $sql = "INSERT IGNORE INTO `css-version`(`link-address`, `version-number`, `feature`, `original`) VALUES(:link, :version, :feature, :original)";
        $stmt = $conn->prepare($sql, [
            \PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY
        ]);
        $stmt->execute([
            ':link' => $fileName,
            ':version' => $checkCss3,
            ':feature' => $features[0],
            ':original' => $cssPath
        ]);
    }

    private function cssRule() {
        $rules = [
            'border' => [
                'border-radius', 'border-image',
            ],
            'background' => [
                'background-image', 'background-position', 'background-repeat',
            ],
            'color' => [
                'rgba', 'hsl', 'hsla', 'opacity',
            ],
            'linear' => [
                'linear-gradient', 'radial-gradient',
            ],
            'shadows' => [
                'text-shadow', 'box-shadow',
            ],
            'text' => [
                'text-overflow', 'word-wrap', 'word-break',
            ],
            'font' => [
                '@font-face',
            ],
            'transform' => [
                'translate(', 'rotate(',
                'rotateX(', 'rotateY(', 'rotateZ(',
                'scale(', 'skewX(', 'skewY(',
                'matrix(', 'transition', '@keyframes',
                'animation',
            ],
            'column' => [
                'filter', 'column-',
            ],
            'box-size' => [
                'resize', 'outline-offset', 'box-sizing',
            ],
            'flex' => [
                'flex', 'align-', 'order', 'space-',
            ],
            'mediaquery' => [
                'min-width'
            ],
        ];

        return $rules;
    }

    private function browserTime() {

    }

    private function anaDomHierarchy() {
        $files = [];
        foreach($this->filePath['paths'] as $val) {
            $fileDir = $val;
            if(file_exists($fileDir.'/index.html')) {
                $files[] = $fileDir.'/index.html';
                @mkdir($fileDir.'/images');
            } else {
                echo $fileDir.'/index.html is not existed...'.PHP_EOL;
            }
        }

        $this->parseHtml($files);
    }

    private function connectDb() {
        $config = [
            'db_type' => 'mysql',
            'db_host' => 'localhost',
            'db_name' => 'web-analyzer',
            'db_username' => 'web-analyzer',
            'db_password' => 'd5shJjww5zHCFNMr' 
        ];
        $db = new Database($config);

        return $db;
    }

    private function parseHtml(array $files) {
        foreach($files as $filePath) {
            $html = file_get_contents($filePath);
            $crawler = new Crawler($html);
            try {
                $this->result = [];
                $crawler->filter('img')->each(function(Crawler $node) {
                    $this->result[] = $node->attr('src');
                });
                foreach($this->result as $imgPath) {
                    $this->storeImg($imgPath, $filePath);
                }
            } catch(\InvalidArgumentException $e) {
                $e->getMessage();
                continue;
            }
        }
        $this->db = null;
    }

    private function storeImg($imgPath, $filePath) {
        $filePath = str_replace('/index.html', '', $filePath);
        $fileName = $this->downloadFile($imgPath, $filePath, 'image');
        if($fileName === null){
            return null;
        }
        $sql = "INSERT IGNORE INTO `images`(`url-link`, `original-img-size`, `compressed-img-size`, `type`) VALUES(:link, :original, :compress, :type)";
        if(isset($fileName) && $fileName !== '') {
            $pathArr = pathinfo($fileName);
            if(isset($pathArr['extension'])) {
                $compFileName = $pathArr['dirname'].'/'.$pathArr['filename'].'-compress.'.$pathArr['extension'];
                if(file_exists($compFileName)) {
                    unlink($compFileName);
                }
                $imageType = 'png';
                $fileCompSize = 0;
                $fileSize = @filesize($fileName);
                if(empty($fileSize)) {
                    return;
                }
                if(stristr($pathArr['extension'], 'jpg') !== false) {
                    exec('jpegoptim --quiet --stdout '.$fileName.' > '.$compFileName, $out, $exitCode);
                    if($exitCode !== 0) {
                        echo 'using jpegoptim to compress'.$fileName.' is failed...'.PHP_EOL;
                    } else {
                        $fileCompSize = filesize($compFileName);
                    }
                    $imageType = 'jpg';
                }
                if(stristr($pathArr['extension'], 'png') !== false) {
                    exec('optipng -quiet '.$fileName.' -out '.$compFileName, $out, $exitCode);
                    if($exitCode !== 0) {
                        echo 'using optipng to compress'.$fileName.' is failed...'.PHP_EOL;
                    } else {
                        $fileCompSize = filesize($compFileName);
                    }
                }
                $conn = $this->db;
                $stmt = $conn->prepare($sql, [
                    \PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY
                ]);
                $stmt->execute([
                    ':link' => $fileName,
                    ':original' => filesize($fileName),
                    ':compress' => $fileCompSize,
                    ':type' => $imageType,
                ]);
            }
        }
    }

    private function downloadFile($reqUrl, $filePath, $type) {
        if(stristr($reqUrl, 'http') === false
                && stristr($reqUrl, 'https') === false
                && $type !== 'image') {
            $reqUrl = 'http:'.$reqUrl;
        }
        $ch = curl_init($reqUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($type === 'image') {
            $saveto = $filePath.'/images';
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        } else if($type === 'css') {
            $saveto = $filePath.'/css';
        } else {
            $saveto = $filePath.'/js';
        }
        @mkdir($saveto);
        $raw = curl_exec($ch);
        curl_close($ch);
        $filePathArr = explode('/', $reqUrl);
        if(stristr($filePathArr[count($filePathArr)-1], '?') !== false) {
            $arrPath = explode('?', $filePathArr[count($filePathArr)-1]);
            $filePathArr[count($filePathArr)-1] = $arrPath[0];
        } else {
            if(stristr($reqUrl, '/') === false && $type !== 'image') {
                return null;
            }
        }
        $saveto .= '/'.$filePathArr[count($filePathArr)-1];
        $infoArr = pathinfo($saveto);
        if(file_exists($saveto) && $filePathArr[count($filePathArr)-1] !== '') {
            unlink($saveto);
        }
        if(empty($infoArr['extension']) === false) {
            file_put_contents($saveto, $raw);
        }
        if(!is_dir($saveto)) {
            return $saveto;
        }

        return null;
    }
}
