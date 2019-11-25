<?php

namespace Borgogelli;

use Borgogelli\MyRecursiveFilterIterator;

/**
 * @todo: stampare elapsed time, inoltre nel log finale, riportare i dati di input, relativi ai percorsi analizzati, le opzioni selezionate e la data
 * @todo: aggiungere file di config dove indicare percorsi da analizzare, eventuali filtri per estensione, e per dimensioni (in caso di superamento di tale limite, non si calcola l'hash ma il record dati va comunque salvato)
  *
 */
class Scanner {

    protected $climate = null;
    private $paths = [];
    private $extensions = null;
    protected $out = null;
    protected $append = false;
    protected $update = false;

    private $records_count = 0;
    private $max_records_count = 0;
    protected $stored_data = [];

    protected $stats = [
        'start' => null,
        'end' => null,
        'elapsed' => null,
        'added' => 0,
        'removed' => 0,
        'hash' => 0,
    ];

    function __construct() {
    $this->climate = new \League\CLImate\CLImate;
    }


    public function printDone(){
        // path analizzati
        // stats:
        //  - hash calcolato
        //  - file aggiunti
        //  - file rimossi
        // elapsed time
    }

    public function run(){

        if($this->update){
            $backup_file = $this->out . '.bak';
            if (!copy($this->out, $bakcup_file)) {
                $this->climate->shout('ERROR: failed to copy' . $this->out);
            }
            if(!is_file($backup_file)){
                $this->climate->shout('ERROR: file not found ' . $backup_file);
                die("QUIT");
            }else{
                $this->climate->info('INFO: data backed up successfully to' . $backup_file);
            }

           if(false){ // NON OTTIMIZZATO
            $content = file_get_contents($backup_file);
            $_stored_data = explode(PHP_EOL, $content);
            $_stored_data2 = [];
            foreach ($this->stored_data as $line) {
                $data = json_decode($line, true);
                $path = $data['path'];
                if(is_file($path)){
                    $sha1 = $data['hash'];
                    if(!$sha1){
                       $sha1 = sha1_file($path);
                       $data['hash'] = $sha1;
                    }
                    $new_line = json_encode($data);
                    $_stored_data2[] = $new_line;
                }
            }
           $content = implode(PHP_EOL, $_stored_data2);
           file_put_contents($this->out, $content);

            }else{ // ALTE PRESTAZIONI


           file_put_contents($this->out, ''); // azzero il file
           $this->climate->shout('NOTICE: file deleted after backed up ' . $this->out);
           $fn = fopen($backup_file, 'r');
           while(! feof($fn))  {
             $line = trim(fgets($fn));
             if($line){
             $data = json_decode($line, true);
             $path = $data['path'];
             if(is_file($path)){
                $sha1 = $data['hash'];
                 if(!$sha1){
                    $this->climate->shout('NOTICE: hash assente per ' . $path);
                    $sha1 = sha1_file($path);
                    $data['hash'] = $sha1;
                    $this->climate->shout('NOTICE: hash ricalcolato per ' . $path);
                 }
                 $new_line = json_encode($data);
                $this->appendLineToFile($new_line, $this->out);
             }
             }
           }
           fclose($fn);

        }

           $this->climate->shout('Backup: ' . $bakcup_file);
           $this->climate->shout('Old records: ' . count($_stored_data));
           $this->climate->shout('New records: ' . count($_stored_data2));
           // La differenza old-new sono i file cancellati
           $this->climate->shout('Done');
           if(!$this->append){
                return true;
           }
        }


            foreach($this->paths as $path){
                $this->scan($path);
            }
            $this->climate->shout('Done');

    }


    private function checkDir($path){
        if(!is_dir($path)){
            $this->climate->error('ERROR: ' . $path . ' is not readable.');
            exit(1);
        }
        if(!is_readable($path)){
            $this->climate->error('ERROR: ' . $path . ' is not writable.');
            exit(1);
        }
    }

    /**
     * $ext string the file extension
     * Nota, l'estensione deve essere specificata senza il punto di prefisso
     */
private function processExtension($ext){
    die("QUIT (remove me e scrivere specifiche per il metodo setExtensions() se deve avere punto oppure no): " . $ext);
    $b = in_array($ext, $this->extensions);
    return $b;
}

private function existsOnData(\SplFileInfo $fileinfo){
    $found = false;
    $path_name = $fileinfo->getPathname();
    if($path_name){
    if(!$this->stored_data){
        $content = file_get_contents($this->out); // Per ottenere maggiori prestazioni lavoro direttamente in RAM, non leggo il file riga per riga ogni volta
        $this->stored_data = explode(PHP_EOL, $content);
    }
    foreach ($this->stored_data as $line) {
        $json = json_decode($line, true);
        if($json['path'] == $path_name){
            $found = true;
            $this->climate->shout('File: ' . $path_name . ' already stored');
            break;
        }
    }
    }
    return $found;
}

private function shouldProccess(\SplFileInfo $fileinfo){
    $b=false;
    // is_link() - Tells whether the filename is a symbolic link
    if($fileinfo->isLink()){
        $path_name = $fileinfo->getPathname();
        $this->climate->shout('Notice: ' . $path_name . ' skipped becasue is a link');
    }else if($fileinfo->isFile()){ // If you pass a symlink (unix symbolic link) as parameter, is_file will resolve the symlink and will give information about the refered file.

        $b2 = false;
        if($this->extensions != null && count($this->extensions)>0){
            $ext = $fileinfo->getExtension();
            $b2 = $this->processExtension($ext);
        }else{
            $b2 = true;
        }

        if($b2){
           if($this->append){
                if(!$this->existsOnData($fileinfo)){
                    $b=true;
                }else{
                    $filename = $fileinfo->getFilename();
                    $this->climate->debug('DEBUG: ' . $filename . ' skipped, info already exists');
                }
            }else{
                $b=true;
            }
        }

    }else{
        $filename = $fileinfo->getFilename();
        $this->climate->shout('NOTICE: ' . $filename . ' is not a file');
    }
    return $b;
}

/**
 *
 * RecursiveDirectoryIterator di default non include i link simbolici.
 * You can use \RecursiveDirectoryIterator::FOLLOW_SYMLINKS (che è ereditata da \FilesystemIterator::FOLLOW_SYMLINKS) as a flag to the constructor to have RecursiveDirectoryIterator follow symlinks, which it does not do by default.
 * @see https://www.php.net/manual/en/class.recursivedirectoryiterator.php
 * Nota: per concatenare più opzioni è necessario usare il Bitwise operator (ovvero il carattere pipe)
 *
 */
private function scan($path){
    $this->climate->red('path: ' . $path);
    $this->checkDir($path);
    $directory = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
    $filter = new MyRecursiveFilterIterator($directory);
    $iterator = new \RecursiveIteratorIterator($filter);
    foreach ($iterator as $fileinfo) {
        // var_dump($fileinfo); // SplFileInfo see https://www.php.net/manual/en/class.splfileinfo.php

        echo "The object \$fileinfo is of type " . get_class($fileinfo) . PHP_EOL;

        if($this->shouldProccess($fileinfo)){
            $this->process($fileinfo);
        }

    }
}

/**
 * @deprecated not used
 */
private function scanDepth0($path){
    $this->climate->red('path: ' . $path);
    $this->checkDir($path);
    $dir = new \DirectoryIterator($path);
    foreach ($dir as $fileinfo) {
        // var_dump($fileinfo); // DirectoryIterator extends SplFileInfo see https://www.php.net/manual/en/class.directoryiterator.php
        if (!$fileinfo->isDot()) {
            if($fileinfo->isFile()){
                 $this->process($fileinfo);
            }else{
                $filename = $fileinfo->getFilename();
                $this->climate->red('NOTICE: ' . $filename . ' is not a file');
            }
        }
    }
}

/**
 * @deprecated not used
 */
private function scan4($path){
    $this->climate->red('path: ' . $path);
    $this->checkDir($path);
    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $fileinfo) {
    // var_dump($fileinfo); // SplFileInfo see https://www.php.net/manual/en/class.splfileinfo.php
        if($fileinfo->isFile()){
                $this->process($fileinfo);
        }else{
            $filename = $fileinfo->getFilename();
            $this->climate->shout('NOTICE: ' . $filename . ' is not a file');
        }
    }
}


private function process(\SplFileInfo $fileinfo){
    if($this->max_records_count>0 && $this->records_count>=$this->max_records_count){
        $this->climate->shout('NOTICE: max records count reached (' . $this->records_count . ')');
        exit(0);
    }
    try {

        $filename = $fileinfo->getFilename();
        $ext = $fileinfo->getExtension();
        $timestamp = $this->getFileCreationDate($fileinfo);
        $date_time = \DateTime::createFromFormat( 'U', $timestamp );
        $date_formatted = $date_time->format( 'c' ); // 'c' is ISO 8601 date, see https://www.php.net/manual/en/function.date.php
        $bytes = $fileinfo->getSize();
        $path_name = $fileinfo->getPathname();
        $sha1 = sha1_file($path_name);
        $info = finfo_file(finfo_open(FILEINFO_MIME), $path_name);

        // JSON DATA

        $data = [];
        $data['path'] = $path_name;
        $data['ext'] = $ext;
        $data['bytes'] = $bytes;
        $data['timestamp'] = $timestamp;
        $data['hash'] = $sha1;
        $data['info'] = $info;

        $json = json_encode($data);
        $this->climate->info('json: ' . $json);
        $this->climate->json($data);

        $this->appendLineToFile($json, $this->out);

        // PRINT TO CONSOLE

        $this->climate->info('path_name: ' . $path_name);
        $this->climate->info('filename: ' . $filename);
        $this->climate->info('ext: ' . $ext);
        $this->climate->info('size: ' . $this->formatBytes($bytes));
        // $this->climate->info('unix timestamp: ' . $timestamp);
        $this->climate->info('changed at: ' . $this->datetimeToMysql($date_time));
        // $this->climate->info('changed at: ' . $date_formatted);
        $this->climate->info('hash: ' . $sha1);
        $this->climate->info('info: ' . $info);

    } catch (\Throwable $th) {
        //throw $th;
        $this->climate->error('ECCEZIONE: ' . $th->getMessage());
    }


}

private function dateToMysql(int $day, int $month, int $year): string {
    $dt = new \DateTime();
    $dt->setTimezone(new \DateTimeZone('UTC'));
    $dt->setDate($year, $month, $day);
    $dt->setTime(0, 0, 0, 0); // set tine to midnight

    return $dt->format("Y-m-d H:i:s");
}
private function datetimeToMysql(\DateTime $dt): string {
    $dt->setTimezone(new \DateTimeZone('UTC'));
    return $dt->format("Y-m-d H:i:s");
}
private function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    // Uncomment one of the following alternatives
    // $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow));

    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Use filectime. For Windows it will return the creation time, and for Unix the change time which is the best you can get because on Unix there is no creation time (in most filesystems).
 *
 */
private function getFileCreationDate(\SplFileInfo $fileinfo){
    return  $fileinfo->getCTime();
}
public function setSinglePath(string $path){
    $this->paths = [$path];
}
public function setMultiPath(array $paths){
    $this->paths = $paths;
}

/**
 * L'estensioni vanno specificate senza il '.' di prefisso
 */
public function setExtensions(array $extensions){
    $this->extensions = $extensions;
}

private function appendLineToFile($line, $out_file){
    $fp = fopen($out_file, 'a'); // opens file in append mode
    fwrite($fp, $line . PHP_EOL);
    $this->records_count++;
    fclose($fp);
}

private function toBoolean($str){
    $str = trim(strtolower($str));
if(
    $str == 'y' ||
    $str == 'yes' ||
    $str == 's' ||
    $str == 'si' ||
    $str == '1'
    ){
return true;
}
return false;
}

private function confirm($question, $default=false){
    $default_answer = 'n';
    if($default){
        $default_answer = 'y';
    }
    $input = $this->climate->input($question);
    $input->defaultTo($default_answer);
    $confirmed = $this->toBoolean($input->prompt());
    return $confirmed;
}

public function setOut(string $file){
    $this->out = $file;

    if(is_file( $this->out)){ // il file esiste

        $confirmed = false;
        if(true){
            $question = 'Vuoi verificare eventuali cancellazioni [d], sovrascriverlo [o] oppure vuoi continuare [a] ?';


            // [c] Clean
            // [a] Append
            // [r] Renew (clean + append)
            // [o] Overwrite (forse è meglio eliminarla questa opzione)
            // Segli

            $input = $this->climate->confirm($question);
            $answer = $input->prompt();
            switch ($answer) {
                case 'd':
                    $this->update = true;
                    break;
                case 'a':
                    $this->update = false;
                    $this->append = true;
                    break;
                case 'r':
                    $this->update = true;
                    $this->append = true;
                    break;
                case 'o':
                    $question = 'Sei sicuro di voler cancellare tutto ?';
                    $input = $this->climate->confirm($question, true);
                    $confirmed = $input->confirmed();
                    if(!$confirmed){
                        $this->climate->shout('Bye');
                        exit(0);
                    }else{
                        file_put_contents( $this->out, '');
                        $this->climate->shout('NOTICE: file deleted ' . $this->out);
                        $this->update = false;
                        $this->append = true;
                    }
                    break;
                default:
                    $this->climate->error('Risposta ' . $answer . ' non valida.');
                    exit(1);
                    break;
            }
        }else if(false){
            $default = 'y';
            $question = 'Vuoi sovrascriverlo [' . $default  . '] ?';
            $confirmed = $this->confirm($question, true);
            if($confirmed){
                file_put_contents( $this->out, '');
            }
        }else if(false){
            $question = 'Vuoi sovrascriverlo ?';
            $input = $this->climate->confirm($question, true);
            $confirmed = $input->confirmed();
            if($confirmed){
                file_put_contents( $this->out, '');
            }
        }



    }else{ // il file non esiste

        file_put_contents( $this->out, '');
        if(!is_file( $this->out)){
            $this->climate->error('Can\'t create the file ' .  $this->out);
        }

    }

    if (is_writable( $this->out)) {
        $this->climate->info('The file ' .  $this->out . ' is writable.');
    } else {
        $this->climate->error('The file ' .  $this->out . ' is not writable.');
    }

}


private function isEqualPath($path1, $path2){
if($path1===$path2){
    return true;
}
$path1 = str_replace("\\\\", "/", $path1);
$path2 = str_replace("\\\\", "/", $path2);
$path1 = str_replace("\\", "/", $path1);
$path2 = str_replace("\\", "/", $path2);
if($path1===$path2){
    return true;
}
return false;
}

public  function fetchData($path_name){
$results = [];
$fn = fopen($this->out, 'r');
while(! feof($fn))  {
  $line = trim(fgets($fn));
  if($line){
  $data = json_decode($line, true);
  $current_path_name = $data['path'];
  if($this->isEqualPath($current_path_name, $path_name) ){
        $results = $data;
        break;
  }
}
}
fclose($fn);
return $results;

}


/**
 * @todo implementare save results to file
 */
public function fetchWrongExtension(){ // file con estensione errata
    $results = [];
    $fn = fopen($this->out, 'r');
    while(! feof($fn))  {
      $line = trim(fgets($fn));
      if($line){
      $data = json_decode($line, true);
      $path_name = $data['path'];
      $ext = $data['ext'];
      $info = $data['info'];
      $ext2 = $this->getRightExtension($info);
      $found = false;
      foreach($ext2 as $key => $value2){
        if($ext == $value2){
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
    fclose($fn);
    return $results;
}


private function getRightExtension($info): array {
    $ext = ['unknown'];
    $map = [
        'audio/x-flac' => ['flac'],
        'application/pdf' => ['pdf'],
        'audio/mpeg' => ['mp3'] ,
        'text/plain' => ['txt'],
        'image/jpeg' => ['jpg', 'jpeg']
    ];
    $tokens = explode(';', $info);
    $mimetype = $tokens[0];
    if(isset($map[$mimetype])){
        $ext = $map[$mimetype];
    }else{
        echo "can't determine the right extension for the mimetype : " . $mimetype   . PHP_EOL;
    }
    return $ext;
}

/**
 * @todo implementare save results to file
 */
public function fetchSameName(){ // omonimie
    $results_filtered = [];
    $results = [];
    $fn = fopen($this->out, 'r');
    while(! feof($fn))  {
      $line = trim(fgets($fn));
      if($line){
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
    fclose($fn);

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
public function fetchSameHash(){ // file duplicati ma con nome differente
    $results_filtered = [];
    $results = [];
    $fn = fopen($this->out, 'r');
    while(! feof($fn))  {
      $line = trim(fgets($fn));
      if($line){
      $data = json_decode($line, true);
      $path_name = $data['path'];
      $hash = $data['hash'];


      $files = [];
      if(isset($results[$hash])){
          $files = $results[$hash];
      }
      $files[] = $path_name;
      $results[$hash] = $files;

        }
    }
    fclose($fn);
    foreach($results as $hash => $files){
        // echo "key: " . $hash . " value " . implode(',', $files) . PHP_EOL;
        if(count($files)>1){
            $results_filtered[$hash] = $files;
        }
    }
    return $results_filtered;
}



} // end class