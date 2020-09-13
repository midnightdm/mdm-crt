<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}

/* * * * * *
 * crtdaemon CLI app runs with command >php crtdaemon.php
 * crtdaemon.php
 * 
 */

// * * * Constant Definitions * * * 
//Marker Alpha Lat is 3 mi upriver Lock 13
define('MARKER_ALPHA_LAT', 41.938785);

//Marker Bravo Lat is Lock 13
define ('MARKER_BRAVO_LAT', 41.897258);

//Marker Charlie Lat is RR bridge
define ('MARKER_CHARLIE_LAT', 41.836353);

//Marker Delta Lat is 3 mi downriver RR bridge
define('MARKER_DELTA_LAT', 41.800704);

// * * * Function Defintions * * *

//function to autoload class files upon instantiation
function myAutoLoader($className) {
    $path      = 'classes\\';
    $extension =  '.class.php';
    $fullPath  = $path . $className . $extension;
    echo "   Loading " . $fullPath . '\\n\\n';
    if(!file_exists($fullPath)) {
        return false;
    }
    include_once($fullPath);
}

//function to grab page using cURL
function grab_page($url, $query='') {
    echo "  grab_page() started  ";
    $ch = curl_init();
    $ua = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:79.0) Gecko/20100101 Firefox/79.0";
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 40);
    curl_setopt($ch, CURLOPT_URL, $url.$query);
    //ob_start();
    return curl_exec($ch);
    //ob_end_clean();
    curl_close($ch);
}  

//Has server specific 'hard-set' file path
function saveImage($mmsi) {
    $url = 'https://www.myshiptracking.com/requests/getimage-normal/';
    $imgData = grab_page($url.$mmsi.'.jpg');
    //$path = getEnv('MDM_CRT_VESSEL_IMAGES_PATH').'mmsi';    
    
    /*
    $path = $_SERVER['DOCUMENT_ROOT'].'../images/vessels/mmsi';
    echo 'running saveImage... ';
    echo '\n   ...$path:' . $path.$mmsi.'.jpg';
    
    @ $file = fopen($path.$mmsi.'.jpg', 'w');
    if(!$file) {
        echo "Error writing file mmsi" . $mmsi . ".jpg";
        return false;
    }
    fwrite($file, $imgData);
    fclose($file);
    */

    //New write code
    $s3 = new Aws\S3\S3Client([
        'version' => 'latest',
        'region' => 'us-east-2'
      ]);
    $bucket = getEnv('S3_BUCKET');
    $fileName = 'vessels/mmsi'.$mmsi.'.jpg';
    $s3->upload($bucket, $fileName, json_encode($data));
    return true;
}

// * * *  Start of App * * *
//Stops unauthorized running

//$str = "Start"; //the damned thing!";
//$msg = "Unable to run crtdaemon.php\n\n";
//echo "Enter passphrase: ";
//$input = trim(fgets(STDIN, 1024));
//if($input != $str) {
//    die($msg);
//} 
echo "\nStarting...\n";

//Load S3 classes
require_once('vendor/autoload.php');


//Load classes as needed
spl_autoload_register('myAutoLoader');


//Create then start instance of CRTdaemon class that runs as a loop
//$daemon = new CRTdaemon(getEnv('MDM_CRT_CONFIG_PATH'));
$daemon = new CRTdaemon($_SERVER['DOCUMENT_ROOT'].'crtconfig.php');

$daemon->start();
echo "crtdaemon started\n\n";