<?php

namespace Borgogelli;

use Borgogelli\MyRecursiveFilterIterator;

/**
 * @todo: stampare elapsed time, inoltre nel log finale, riportare i dati di input, relativi ai percorsi analizzati, le opzioni selezionate e la data
 * @todo: aggiungere file di config dove indicare percorsi da analizzare, eventuali filtri per estensione, e per dimensioni (in caso di superamento di tale limite, non si calcola l'hash ma il record dati va comunque salvato)
 *
 * @see https://stackoverflow.com/questions/12077177/how-does-recursiveiteratoriterator-work-in-php
 *
 * @see https://en.wikipedia.org/wiki/Depth-first_search
 * @see https://en.wikipedia.org/wiki/Breadth-first_search
 *
 * @see https://gist.github.com/thinkphp/1439637/f5ee763a94b6ae8e99e4fa5709bb89f08fba237e
 * @see https://gist.github.com/DmitrySoshnikov/63f9acfac4651da5d21f
 *
 */
class Scanner {

    protected $countItemsBeforeStart = false; // se "true" conto il numero di file complessivo prima di iniziare la scansione (l'operazione è facoltativa perchè potrebbe essere onerosa in termini di pretazioni se i file sono numerosi)
    protected $climate = null;
    private $paths = [];
    private $extensions = null;
    protected $out = null;
    protected $append = false;
    protected $update = false;

    private $records_count = 0;
    private $max_records_count = 0; // opzionale
    protected $stored_data = [];

    private $start_from = 118892;

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

/**
 * @todo gestire $this->stats
 */
    public function printStats(){
        // path analizzati
        // stats:
        //  - hash calcolato
        //  - file aggiunti
        //  - file rimossi
        // elapsed time
    }

    private function update(){
        $backup_file = $this->out . '.bak';
        if (!copy($this->out, $backup_file)) {
            $this->error('failed to copy' . $this->out);
        }
        if(!is_file($backup_file)){
            $this->error('file not found ' . $backup_file);
            die("QUIT");
        }else{
            $this->info('data backed up successfully to' . $backup_file);
        }



       file_put_contents($this->out, ''); // azzero il file
       $this->notice('file deleted after backed up ' . $this->out);
       $fn = fopen($backup_file, 'r');
       while($fn && !feof($fn)){
         $line = fgets($fn);
         if($line){
            $line = trim($line);
         $data = json_decode($line, true);
         $path = $data['path'];
         if(is_file($path)){
            $sha1 = $data['hash'];
             if(!$sha1){
                $this->notice('hash assente per ' . $path);
                $sha1 = sha1_file($path);
                $data['hash'] = $sha1;
                $this->notice('hash ricalcolato per ' . $path);
             }
             $new_line = json_encode($data);
            $this->appendLineToFile($new_line, $this->out);
         }
         }
       }
       if($fn){
       fclose($fn);
       }



       $this->climate->br();
       $this->climate->flank('UPDATE PROCEDURE STATISTICS', '#', 6);
       $this->climate->out("");
       $this->climate->shout('Backup file: ' . $backup_file);
       $this->climate->shout('Data file: ' . $this->out);

       $this->climate->br();
       // La differenza old-new sono i file cancellati
       $this->climate->shout('Update done');
       $this->climate->br();
    }

    public function run(){
        $this->climate->clear();
        if($this->update){
            $this->update();
        }

        if($this->append){
            $tot = count($this->paths);
            $n = 0;
            foreach($this->paths as $path){
                $n++;
                $this->climate->flank("Analyzing path $n/$tot | " . $path);
                $this->scan($path);
            }
        }
        $this->climate->shout('All done');
    }

    private function checkDir($path){
        if(!is_dir($path)){
            $this->error($path . ' is not readable.');
            exit(1);
        }
        if(!is_readable($path)){
            $this->error($path . ' is not writable.');
            exit(1);
        }
    }


public function demo(){

    $colors = [
    'Black',
    'Red',
    'Green',
    'Yellow',
    'Blue',
    'Magenta',
    'Cyan',
    'Light Gray',
    'Dark Gray',
    'Light Red',
    'Light Green',
    'Light Yellow',
    'Light Blue',
    'Light Magenta',
    'Light Cyan',
    'White'
    ];

    foreach ($colors as $color) {
        $color = strtolower($color);
        $color = str_replace(" " , "_" , $color);
        $this->climate->out("################################");
        $this->climate->out("$color <$color>Hello World !</$color>");
        $this->climate->bold()->out("bold $color <$color>Hello World !</$color>");
        $this->climate->dim()->out("dim $color <$color>Hello World !</$color>");
        $this->climate->underline()->out("underline $color <$color>Hello World !</$color>");
        $this->climate->blink()->out("blink $color <$color>Hello World !</$color>");
        $this->climate->invert()->out("invert $color <$color>Hello World !</$color>");
        $this->climate->hidden()->out("hidden $color <$color>Hello World !</$color>");
    }
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
            // $this->climate->shout('File: <light_yellow>\'' . $path_name . '\'</light_yellow> already stored');
            break;
        }
    }
    }
    return $found;
}

private function shouldProccess(\SplFileInfo $fileinfo){
    $b=false;
    // is_link() - Tells whether the filename is a symbolic link

    $path_name = $fileinfo->getPathname();
    // $file_name = $fileinfo->getFilename();

    if($fileinfo->isLink()){
        $this->notice("file skipped | $path_name | is a link");
    }else if($fileinfo->isFile()){ // If you pass a symlink (unix symbolic link) as parameter, is_file will resolve the symlink and will give information about the refered file.

    if($this->append){
        if(!$this->existsOnData($fileinfo)){
            $b=true;
        }else{

            $this->info("file skipped | $path_name | info already stored");
            // ovviamente $b=false;
        }
    }else{
        $b=true;
    }

    }else{
        $this->warn("file skipped | $path_name | is not a file");
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
    $this->checkDir($path);
    $directory = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
    $filter = new MyRecursiveFilterIterator($directory);
    $iterator = new \RecursiveIteratorIterator($filter);

    $tot = 0;
    if($this->countItemsBeforeStart){
        $tot = iterator_count($iterator);
    }else{
        $this->climate->debug("Count all elements skipped.");
    }
    $n = 0;
    foreach ($iterator as $fileinfo) {
        $n++;
        $path_name = $fileinfo->getPathname();

        if($this->start_from>0 && $n<$this->start_from){
            $this->climate->flank("$n / $tot skipping...");
            continue;
        }


        $this->climate->flank($this->getProgressMsg($n, $tot) . " | " . $path_name);

        // var_dump($fileinfo); // SplFileInfo see https://www.php.net/manual/en/class.splfileinfo.php

        // echo "The object \$fileinfo is of type " . get_class($fileinfo) . PHP_EOL;


        if($this->shouldProccess($fileinfo)){
            $this->process($fileinfo);
        }

    }
}
private function getProgressMsg($n, $tot){
    $msg = null;
    if($this->countItemsBeforeStart){
        $msg = "$n/$tot (" . $this->formatAsPerc($n, $tot) . ")";
    }else{
        $msg = "$n/?";
    }
    return $msg;
}

private function formatAsPerc($n, $tot){
    $float = ($n/$tot)*100;
    $formatted = number_format($float, 2,',','');
    return $formatted . " %";

}

private function process(\SplFileInfo $fileinfo){
    if($this->max_records_count>0 && $this->records_count>=$this->max_records_count){
        $this->notice('max records count reached (' . $this->records_count . ')');
        exit(0);
    }
    try {

        $filename = $fileinfo->getFilename();
        $ext = strtolower($fileinfo->getExtension());
        $timestamp = $this->getFileCreationDate($fileinfo);
        $bytes = $fileinfo->getSize();
        $path_name = $fileinfo->getPathname();
        // $this->info("Analyzing the file \'$path_name\' ...");

        $date_time = \DateTime::createFromFormat( 'U', $timestamp );
        $date_formatted = $date_time->format( 'c' ); // 'c' is ISO 8601 date, see https://www.php.net/manual/en/function.date.php
        $sha1 = sha1_file($path_name);
        $info = finfo_file(finfo_open(FILEINFO_MIME), $path_name); // @see https://www.php.net/manual/en/fileinfo.constants.php

        // JSON DATA

        $data = [];
        $data['path'] = $path_name;
        $data['ext'] = $ext;
        $data['bytes'] = $bytes;
        $data['timestamp'] = $timestamp;
        $data['hash'] = $sha1;
        $data['info'] = $info;


        // Print to console
        //if(false){
        // $json_pretty = json_encode($data, JSON_PRETTY_PRINT); // @see https://www.php.net/manual/en/function.json-encode.php
        // $this->info('json: ' . $json_pretty);
        // ...oppure...
        // $this->climate->json($data); // preferibile allo statement precedente
        //}

        // Save data
        $json = json_encode($data);
        $this->appendLineToFile($json, $this->out);

        // Print to console
        $this->info("file '$path_name'");
        //$this->info("filename: '$filename'");
        $this->info('size ' . $this->formatBytes($bytes));
        $this->info('changed at ' . $this->datetimeToMysql($date_time));
        // $this->info('unix timestamp: ' . $timestamp);
        // $this->info('changed at: ' . $date_formatted);
        $this->info('hash (sha1) ' . $sha1);
        $this->info('type ' . $ext . ' | ' . $info);

    } catch (\Throwable $th) {
        //throw $th;
        $this->error('exception: ' . $th->getMessage());
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
    if($fp){
    fwrite($fp, $line . PHP_EOL);
    $this->records_count++;
    fclose($fp);
    }
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


            // [d] Update
            // [a] Append
            // [r] Renew (Update + Append)
            // [o] Overwrite (forse è meglio eliminarla questa opzione)


            $input = $this->climate->confirm($question);
            $answer = $input->prompt();
            switch ($answer) {
                case 'd':
                    $this->update = true;
                    $this->append = false;
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
                        $this->info('Bye');
                        exit(0);
                    }else{
                        file_put_contents( $this->out, '');
                        $this->notice('file deleted ' . $this->out);
                        $this->update = false;
                        $this->append = true;
                    }
                    break;
                default:
                    $this->error("'$answer' is not a valid answer");
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
            $this->error('can\'t create the file ' .  $this->out);
        }

    }

    if (is_writable( $this->out)) {
        $this->info('the file ' .  $this->out . ' is writable.');
    } else {
        $this->error('the file ' .  $this->out . ' is not writable.');
    }

}





public function notice($msg){
    $this->climate->invert()->lightBlue()->out('NOTICE: ' . $msg);
}
public function info($msg){
    $this->climate->lightGreen()->out('INFO: ' . $msg);
}
public function warn($msg){
    $this->climate->lightYellow()->out('WARNING: ' . $msg);
}
public function error($msg){
    $this->climate->lightRed()->out('ERROR: ' . $msg);
}

} // end class