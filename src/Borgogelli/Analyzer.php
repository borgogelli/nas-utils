<?php

namespace Borgogelli;

class Analyzer {

    private $data_files = [];

    function __construct(array $data_files) {
        print "In BaseClass constructor\n";
        $this->data_files = $data_files;
    }



private function getRightExtension(string $info): array {
    $exts = [];
    $map = [
        'application/vnd.oasis.opendocument.text' => ['odt'],
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['pptx'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/msword' => ['doc'],
        'application/vnd.ms-powerpoint' => ['ppt'],
        'audio/x-flac' => ['flac'],
        'video/ogg' => ['ogg', 'spx','oga', 'ogv'], // https://it.wikipedia.org/wiki/Ogg
        'audio/ogg' => ['ogg'],
        'application/vnd.ms-opentype' => ['otf'], // https://en.wikipedia.org/wiki/OpenType
        'application/pdf' => ['pdf'],
        'video/x-ms-asf' => ['wma'],
        'application/epub+zip' => ['epub'],
        'audio/mpeg' => ['mp3'] ,
        'text/plain' => ['txt', 'url', 'nfo', 'log', 'ini', 'srt', 'm3u', 'idx'],
        'application/x-msi' => ['msi'],
        'application/java-archive' => ['jar'],
        'application/x-bittorrent' => ['torrent'],
        'text/rtf' => ['rtf'],
        'text/xml' => ['xml'],
        'video/mp4' => ['mp4'],
        'video/quicktime' => ['mp4'],
        'image/png' => ['png'],
        'image/x-ms-bmp' => ['bmp'],
        'image/x-eps' => ['eps'],
        'image/gif' => ['gif'],
        'text/html' => ['htm', 'html'],
        'image/svg+xml' => ['svg'],
        'application/x-font-ttf' => ['ttf'],
        'video/x-matroska' => ['mkv'],
        'video/x-msvideo' => ['avi'],
        'application/x-rar' => ['cbr', 'rar'],
        'application/zip' => ['zip', 'cbz'],
        'application/x-gzip' => ['tgz'],
        'application/x-bzip2' => ['tbz2'],
        'application/x-7z-compressed' => ['7z'],
        'application/x-iso9660-image' => ['nrg', 'iso'],
        'application/x-dosexec' => ['exe'],
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/x-icon' => ['ico'],
        'image/x-tga' => ['tga'],
        'image/x-tgaimage/x-tga' => ['tga'],
        'text/x-php' => ['php'],
        'text/x-po' => ['po'],
        'video/mpeg' => ['mpg', 'vob'],
        'image/vnd.adobe.photoshop' => ['psd'],
        'application/x-shockwave-flash' => ['swf'],
        'application/vnd.ms-cab-compressed' => ['cab'],
        'application/x-sqlite3' => ['sqlite'],
        'application/winhelp' => ['hlp'],
        'audio/x-wav' => ['wav']
    ];
    $mimetype = $this->getMimeType($info);
    if(isset($map[$mimetype])){
        $exts = $map[$mimetype];
        if($mimetype == 'application/x-rar'){
            for ($i = 0; $i < 100; $i++) {
                $exts[] = 'r' . str_pad($i, 2, '0', STR_PAD_LEFT);
            }
            for ($i = 0; $i < 100; $i++) {
                $exts[] = 's' . str_pad($i, 2, '0', STR_PAD_LEFT);
            }
        }
    }else{

    }
    return $exts;
}




private function isEqualPath($path1, $path2){
    if($path1===$path2){
        return true;
    }
     $path1 = str_replace("\\\\\\\\", "\\\\", $path1);
    if($path1===$path2){
        return true;
    }else{

    }
    return false;
    }


public  function fetchData($path_name){
    $results = [];
    foreach ($this->data_files as $file) {
        $fn = fopen($file, 'r');
        while($fn && !feof($fn)){
      $line = fgets($fn);
      if($line){
        $line = trim($line);
      $data = json_decode($line, true);
      $current_path_name = $data['path'];
      if($this->isEqualPath($current_path_name, $path_name) ){
            $results = $data;
            break;
      }
    }
    }
    $this->fclose($fn);
    }
    return $results;

    }

    private function fclose($fn){
        if($fn){
            fclose($fn);
        }
    }

    /*

    $data = [
        [
              'file'       => 'nomefile',
              'left'       => 'path1',
              'right'      => 'path2',
              'hash'      => 'SAME',
              'newer'      => 'LEFT',
              'note'       => '',
        ],
...
    ];






    $data = [
        [
              'file'       => 'nomefile',
              'hash'       => 'count',
              'count'       => 'count',
              'newer path'      => 'count',
              'newer date'       => 'newer + percorso ',
        ],
...
    ];

    $data = [
        [
              'file'       => 'nomefile',
              'mime type'       => 'count',
              'extension'      => 'count',
              'note'       => 'wrong extension',
        ],
...
    ];

    $climate->table($data);


*/


    public function fetchWrongExtension(){ // file con estensione errata
        $results = [];
        foreach ($this->data_files as $file) {
         $fn = fopen($file, 'r');
        while($fn && !feof($fn)){
          $line = fgets($fn);
          if($line){
              $line = trim($line);
          $data = json_decode($line, true);
          $path_name = $data['path'];
          $ext = $data['ext'];
          $info = $data['info'];


          $mimetype = $this->getMimeType($info);
          if(in_array($mimetype, ["application/octet-stream"])){
           // echo "SKIP " . $path_name . PHP_EOL;
          }else if(in_array($ext, ["part"])){
            // echo "SKIP " . $path_name . PHP_EOL;
          }else{
          $ext2 = $this->getRightExtension($info);
          if(empty($ext2)){
            echo "can't determine the right extension for the mimetype : " . $mimetype   . " | Actual extension is " . $ext . PHP_EOL;
          }else{
          $found = false;
          foreach($ext2 as $key => $value2){
            if(strtolower($ext) == strtolower($value2)){ // case insensitive
                $found = true;
                break;
            }
          }

          if(!$found){
              // echo "FIX: ext: " . $ext . " should be [ " . implode(",", $ext2) . ']' . ' file is ' . $path_name . PHP_EOL;
              $results[$path_name] = $ext2;
          }
        }
        }
        }
        }
        $this->fclose($fn);
        }
        return $results;
    }

private function getMimeType(string $info): string{
        $tokens = explode(';', $info);
          $mimetype = $tokens[0];
          return $mimetype;
}


    /**
     * @todo implementare save results to file
     */
    public function fetchSameName(){ // omonimie
        $results_filtered = [];
        $results = [];
        foreach ($this->data_files as $file) {
        $fn = fopen($file, 'r');
        while($fn && !feof($fn)){
          $line = fgets($fn);
          if($line){
            $line = trim($line);
          $data = json_decode($line, true);
          $path_name = $data['path'];
          $path_parts = pathinfo($path_name);
          $file_name = $path_parts['basename'];
          $payload = null;

          if(isset($results[$file_name])){
            $payload = $results[$file_name];
            $occurence = $payload['count'];
            $_paths = $payload['paths'];
            $_paths[] = $path_name;
            $results[$file_name] = [
                'paths' => $_paths,
                'count' => $occurence + 1
            ];
          }else{
            $results[$file_name] = [
                'paths' => [$path_name],
                'count' =>  1
            ];
          }

        }
        }
        $this->fclose($fn);
        }
        // PRINT
        foreach($results as $key => $value){
            $paths = $value['paths'];
            $count = $value['count'];
            // echo "key: " . $key . " value " . $value . PHP_EOL;
            if($count>1){
                $results_filtered[$key] = $value;
            }
        }
        return $results_filtered;
    }

 /**
     * @todo implementare save results to file
     */
    public function fetchSameHash(){
        $results_filtered = [];
        $results = [];
        foreach ($this->data_files as $file) {
        $fn = fopen($file, 'r');
        while($fn && !feof($fn)){
          $line = fgets($fn);
          if($line){
            $line = trim($line);
          $data = json_decode($line, true);
          $path_name = $data['path'];
          $hash = $data["hash"];
          $path_parts = pathinfo($path_name);
          $file_name = $path_parts['basename'];

          $payload = null;

          if(isset($results[$hash])){
            $payload = $results[$hash];
            $occurence = $payload['count'];
            $_paths = $payload['paths'];
            $_paths[] = $path_name;
            $results[$hash] = [
                'paths' => $_paths,
                'count' => $occurence + 1
            ];
          }else{
            $results[$hash] = [
                'paths' => [$path_name],
                'count' =>  1
            ];
          }

        }
        }
        $this->fclose($fn);
        }
        // PRINT
        foreach($results as $key => $value){
            $paths = $value['paths'];
            $count = $value['count'];
            // echo "key: " . $key . " value " . $value . PHP_EOL;
            if($count>1){
                $skip = false;
                    $skip = $this->shouldSkip($paths);
                    if(!$skip){
                        $results_filtered[$key] = $value;
                    }
            }
        }
        return $results_filtered;
    }


    private function shouldSkip($paths){
        $skip = false;
        $prev_bytes = 0;
        foreach ($paths as $file) {
        if(!is_file($file)){
            echo "SKIP: non è un file: " . $file . PHP_EOL;
            $skip = true;
        }
        // Controlli sull'estensione del file
        if(!$skip){
        $path_parts = pathinfo($file);
        if(!isset($path_parts['extension'])){
                echo "SKIP: impossibile determinare l'estensione del file: " . $file . PHP_EOL;
        }else{
            $ext = strtolower($path_parts['extension']);
            if($ext == "url"){
                $skip = true; // I don't know why but I can't get the filezise of a url
            }else if($ext == "bup" || $ext == "ifo"){ // extensions to exclude (are same files)
                $skip = true; // ifo and bup files are identical
            }else if(in_array($ext, ["vob", "pkg"])){ // extensions to exclude (user choice)
                $skip = true; // ifo and bup files are identical
            }
        }
        }

        // COntrolli sulle dimensioni
        if(!$skip){
        $bytes = filesize($file);
        if(!$bytes){
            // die("ZERO bytes: " . $file . PHP_EOL);
             $skip = true;
        }
        if($prev_bytes){
            if($prev_bytes != $bytes){
                die("QUIT: zero bytes " . $prev_bytes . " != " . $bytes . PHP_EOL);
            }
        }
        $prev_bytes = $bytes;

    } // end if
    } // end for
    return  $skip;
    }
} // end class