<?php

require_once __DIR__ . "/vendor/autoload.php";

$dummy = new Borgogelli\Scanner();

if(false){
    $target_path = __DIR__ . DIRECTORY_SEPARATOR . 'resources';
    $dummy->setPath($target_path);
    $paths = [];
    $paths[] = $target_path;
    $dummy->setMultiPath($paths);
    $dummy->setExtensions(['txt', 'jpg']);
    $dummy->setOut($target_path . DIRECTORY_SEPARATOR . 'files.txt');
    $dummy->run();
}
if(false){
    echo "********************* fetchSameName" . PHP_EOL;
    $result = $dummy->fetchSameName();
    print_r($result);
}

if(false){
    echo "********************* fetchSameHash" . PHP_EOL;
    $result = $dummy->fetchSameHash();
    print_r($result);
}
if(false){
    echo "********************* fetchWrongExtension" . PHP_EOL;
    $result = $dummy->fetchWrongExtension();
    print_r($result);
}
if(false){
    echo "********************* fetchData" . PHP_EOL;
    $filename = 'C:\Users\Borgo\workspace-borgo\nas-utils\resources\a\Sample_BeeMoved_96kHz24bit.flac';
    $result = $dummy->fetchData($filename);
    print_r($result);
}


$root_sep = '';
$sep = '';

$ip1 = '192.168.0.249'; // NSA326
//$path1 = "\\\\$ip1\\admin";
$path2 = "\\\\$ip1\\music";
$path3 = "\\\\$ip1\\photo";
$path4 = "\\\\$ip1\\video";

/*
$ip2 = '192.168.0.250'; // NSA325
$path11 = $root_sep . $ip2 . $sep . 'admin';
$path12 = $root_sep . $ip2 . $sep . 'admin2';
$path13 = $root_sep . $ip2 . $sep . 'music';
$path14 = $root_sep . $ip2 . $sep . 'photo';
$path15 = $root_sep . $ip2 . $sep . 'photo2';
$path16 = $root_sep . $ip2 . $sep . 'ps3';
$path17 = $root_sep . $ip2 . $sep . 'public';
$path18 = $root_sep . $ip2 . $sep . 'video';
$path19 = $root_sep . $ip2 . $sep . 'video2';
*/

$paths = [];
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
$dummy->setOut(__DIR__ .  '/resources/' . 'nsa326-files.txt');
$dummy->run();


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