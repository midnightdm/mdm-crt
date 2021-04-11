<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}

/* * * * * *
 * test CLI app runs with command >php test3.php
 * test.php
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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Pusher\PushNotifications\PushNotifications;

// * * * Function Defintions * * *

//function to autoload class files upon instantiation
function myAutoLoader($className) {
    $path      = 'classes/';
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
    echo "Function grab_page() \$url=$url, \$query=$query\n";
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

function getTimeOffset() {
    return date("I") ? -21600 : -18000;
  }
  
function getNow($dateString="Y-m-d H:i:s") {  
    return date($dateString, (time()+getTimeOffset()));
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
    $awsKey      = getEnv('AWS_ACCESS_KEY_ID');
    $awsSecret   = getEnv('AWS_SECRET_ACCES_KEY');
    $credentials = new Aws\Credentials\Credentials($awsKey, $awsSecret);

    $s3 = new Aws\S3\S3Client([
        'version'     => 'latest',
        'region'      => 'us-east-2',
        'credentials' => $credentials
    ]);    

    $bucket = getEnv('S3_BUCKET');
    $fileName = 'vessels/mmsi'.$mmsi.'.jpg';
    $s3->upload($bucket, $fileName, $imgData);
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


//Load S3 classes
$vendorFile = getEnv('HOST_IS_HEROKU') ?  'vendor/autoload.php' :  '../vendor/autoload.php';
require_once($vendorFile); 

/*
//Load classes as needed
//spl_autoload_register('myAutoLoader');
include_once('classes/CRTdaemon.class.php');
include_once('classes/Dbh.class.php');
include_once('classes/LiveScan.class.php');
include_once('classes/LiveScanModel.class.php');
include_once('classes/PassagesModel.class.php');
include_once('classes/Vessel.class.php');
include_once('classes/VesselsModel.class.php');
include_once('classes/ShipPlotter.class.php');
include_once('classes/ShipPlotterModel.class.php');
include_once('classes/Messages.class.php');
include_once('classes/AlertsModel.class.php');
include_once('classes/TimeLogger.class.php');
//include_once('../application/helpers/crtfunctions_helper.php');

*/

//Create then start instance of CRTdaemon class that runs as a loop
//$file = getEnv('HOST_IS_HEROKU') ?  'daemon/crtconfig.php' :  getEnv('MDM_CRT_CONFIG_PATH');
//$daemon = new CRTdaemon($file);

//code to decode ais messages
/*
? Receives a broadcast message,
? Organises the binary bits of the Message Data into 6-bit strings,
? Converts the 6-bit strings into their representative "valid characters" – see IEC 61162-1,
table 7,
? Assembles the valid characters into an encapsulation string, and
? Transfers the encapsulation string using the VDM sentence formatter.

Decoding:

    Sample Demo File: ais.2.decode_sample.php
        call method process_ais_buf(...) and pass in AIS data from a serial or IP source or a test AIS string
        override method process_ais_itu(...) to process the data the way you want to handle it


*/




//$ais = "1P000Oh1IT1svTP2r:43grwb0Eq4";
$ais = "15MvtRd00<qSC?jGvAw@nh`d00ST";

// Include the AIS encoder/decoder
include_once('classes/ais.2.php');

class MyAIS extends AIS {
	// This function is Overridable and is called by process_ais_itu(...) method
	function decode_ais($_aisdata) {
		$ro = new stdClass(); // return object
		$ro->cls = 0; // AIS class undefined, also indicate unparsed msg
		$ro->name = '';
		$ro->sog = -1.0;
		$ro->cog = 0.0;
		$ro->lon = 0.0;
		$ro->lat = 0.0;
		$ro->ts = time();
		$ro->id = bindec(substr($_aisdata,0,6));
		$ro->mmsi = bindec(substr($_aisdata,8,30));
		if ($ro->id >= 1 && $ro->id <= 3) {
			$ro->cog = bindec(substr($_aisdata,116,12))/10;
			$ro->sog = bindec(substr($_aisdata,50,10))/10;
			$ro->lon = $this->make_lonf(bindec(substr($_aisdata,61,28)));
			$ro->lat = $this->make_latf(bindec(substr($_aisdata,89,27)));
			$ro->cls = 1; // class A
		}
		else if ($ro->id == 5) {
			//$imo = bindec(substr($_aisdata,40,30));
			//$cs = $this->binchar($_aisdata,70,42);
			$ro->name = $this->binchar($_aisdata,112,120);
			$ro->cls = 1; // class A
		}
		else if ($ro->id == 18) {
			$ro->cog = bindec(substr($_aisdata,112,12))/10;
			$ro->sog = bindec(substr($_aisdata,46,10))/10;
			$ro->lon = $this->make_lonf(bindec(substr($_aisdata,57,28)));
			$ro->lat = $this->make_latf(bindec(substr($_aisdata,85,27)));
			$ro->cls = 2; // class B
		}
		else if ($ro->id == 19) {
			$ro->cog = bindec(substr($_aisdata,112,12))/10;
			$ro->sog = bindec(substr($_aisdata,46,10))/10;
			$ro->lon = $this->make_lonf(bindec(substr($_aisdata,61,28)));
			$ro->lat = $this->make_latf(bindec(substr($_aisdata,89,27)));
			$ro->name = $this->binchar($_aisdata,143,120);
			$ro->cls = 2; // class B
		}
		else if ($ro->id == 24) {
			$pn = bindec(substr($_aisdata,38,2));
			if ($pn == 0) {
				$ro->name = $this->binchar($_aisdata,40,120);
			}
			$ro->cls = 2; // class B
		}
		var_dump($ro); // dump results here for demo purpose
		return $ro;
	}
}

$ais = new MyAIS();

// Test Single Message
if (1) {
	$buf = "!AIVDM,1,1,,A,15DAB600017IlR<0e2SVCC4008Rv,0*64\r\n";
	// Important Note:
	// After receiving input from incoming serial or TCP/IP, call the process_ais_buf(...) method and pass in
	// the input from device for further processing.
	$ais->process_ais_buf($buf);
}

// Test With Large Array Of Messages - represent packets of incoming data from serial port or IP connection
if (1) {
	$test2_a = array( 
        "!AIVDM,2,1,0,,55MvtR`00001L@;WSOH=8Tm<tr0Lhu9V2222220qa0JD=54nl75Dod6<0wd5j,0*3F\r\n",
        "!AIVDM,2,2,0,,u`88888880,2*48\r\n"
    );    
    
    /*
        "sdfdsf!AIVDM,1,1,,B,18JfEB0P007Lcq00gPAdv?v000Sa,0*21\r\n!AIVDM,1,1,,B,18Jjr@00017Kn",
		"jh0gNRtaHH00@06,0*37\r\n!AI","VDM,1,1,,B,18JTd60P017Kh<D0g405cOv00L<c,0*",
		"42\r\n",
		"!AIVDM,2,1,8,A,55RiwV02>3bLS=HJ220t<D4r0<u84j222222221?=PD?55Pf0BTjCQhD,0*73\r\n",
		"!AIVDM,2,2,8,A,3lQH888888",
		"88880,2*6A\r",
		"\n!AIVDM,2,1,9,A,569w5`02>0V090=V221@DpN0<PV222222222221EC8S@:5O`0B4jCQhD,0*11\r\n!AIVDM,2,2,9,A,3lQH88888888880,2*6B\r\n!AIVDO,1,1,",
		",A,D05GdR1MdffpuTf9H0,4*7","E\r\n!AIVDM,1,1,,A,?","8KWpp0kCm2PD00,2*6C\r\n!AIVDM,1,1,,A,?8KWpp1Cf15PD00,2*3B\r\nUIIII"
	*/
	foreach ($test2_a as $test2_1) {
		// Important Note:
		// After receiving input from incoming serial or TCP/IP, call the process_ais_buf(...) method and pass in
		// the input from device for further processing.
		$ais->process_ais_buf($test2_1);
	}
}

?>