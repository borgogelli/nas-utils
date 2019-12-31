<?php

require_once __DIR__ . "/vendor/autoload.php";

$dummy = new Borgogelli\Scanner();

$climate = new \League\CLImate\CLImate;

// $dummy->demo(); // climate demo

$root_sep = '';
$sep = '';
$paths = [];

if(false){
    $root_sep = '\\\\';
    $sep = '\\';
    $out = __DIR__ .  '/resources/' . 'nsa325-files.txt';
    $ip2 = '192.168.0.250'; // NSA325
    // $path1 = $root_sep . $ip2 . $sep . 'admin';
    // $path2 = $root_sep . $ip2 . $sep . 'admin2';
    // $path3 = $root_sep . $ip2 . $sep . 'music';
    // $path4 = $root_sep . $ip2 . $sep . 'photo';
    // $path5 = $root_sep . $ip2 . $sep . 'photo2';
    // $path6 = $root_sep . $ip2 . $sep . 'ps3';
    // $path7 = $root_sep . $ip2 . $sep . 'public';
    // $path8 = $root_sep . $ip2 . $sep . 'video';
    // $path9 = $root_sep . $ip2 . $sep . 'video2';

    $path101 = $root_sep . $ip2 . $sep . 'volume1';
    $path102 = $root_sep . $ip2 . $sep . 'volume2';

    if(isset($path101)){
        $paths[] = $path101;
    }
    if(isset($path102)){
        $paths[] = $path102;
    }

    if(isset($path1)){
        $paths[] = $path1;
    }
    if(isset($path2)){
        $paths[] = $path2;
    }
        if(isset($path3)){
        $paths[] = $path3;
        }
        if(isset($path4)){
        $paths[] = $path4;
        }
        if(isset($path5)){
            $paths[] = $path5;
            }
            if(isset($path6)){
            $paths[] = $path6;
            }
            if(isset($path7)){
            $paths[] = $path7;
            }
            if(isset($path8)){
            $paths[] = $path8;
            }
            if(isset($path9)){
                $paths[] = $path9;
            }
        }


if(false){
$out = __DIR__ .  '/resources/' . 'nsa326-files.txt';
$ip1 = '192.168.0.249'; // NSA326
// $path1 = "\\\\$ip1\\admin";
 // $path2 = "\\\\$ip1\\music";
$path3 = "\\\\$ip1\\photo";
//$path4 = "\\\\$ip1\\video";

/*

*/


if(isset($path1)){
$paths[] = $path1;
}
if(isset($path2)){
$paths[] = $path2;
}
if(isset($path3)){
$paths[] = $path3;
}
if(isset($path4)){
$paths[] = $path4;
}


$dummy->setMultiPath($paths);
$dummy->setOut($out);
$dummy->run();
}


/*
$array1 = json_decode('{"namespace":"myCompany\\package\\subpackage"}', true);
$array2 = json_decode('{"namespace2":"myCompany\package\subpackage"}', true);
print_r($array1);
print_r($array2);

echo "************************" . PHP_EOL;
$data = [];
$data["namespace"] = "myCompany\\package\\subpackage";
$data["namespace2"] = "myCompany\package\subpackage";
$data["namespace3"] = 'myCompany\\package\\subpackage';
$data["namespace4"] = 'myCompany\package\subpackage';
$json = json_encode($data);
print_r($json);

*/


$data_files = [
 //   __DIR__ .  '/resources/' . 'nsa325-files.txt',
    __DIR__ .  '/resources/' . 'nsa326-files.txt'
];
$analyzer = new Borgogelli\Analyzer($data_files);

if(false){
    echo "********************* fetchSameName" . PHP_EOL;
    $result = $analyzer->fetchSameName();
    printResults($result);
}

if(false){
    echo "********************* fetchSameHash" . PHP_EOL;
    $result = $analyzer->fetchSameHash();
    printResults($result);
}
if(true){
    echo "********************* fetchWrongExtension" . PHP_EOL;
    $result = $analyzer->fetchWrongExtension();
    printResults($result);
}
if(true){
    echo "********************* fetchData" . PHP_EOL;
    $filename = '\\\\192.168.0.249\admin\da sistemare\Andrea\docs\2 maritino\info\tea.png';
    $result = $analyzer->fetchData($filename);
    printResults($result);
}

function printResults($result){
    global $climate;
    $input = $climate->confirm('Continue?');
    if ($input->confirmed()) {
    print_r($result);
    echo "TOT: " . count($result) . PHP_EOL;
    }
}