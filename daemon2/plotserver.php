<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}

//Load all the dependencies
include_once('classes/ais.2.php');
include_once('classes/MyAIS.class.php');
include_once('classes/PlotDaemon.class.php');
include_once('classes/LivePlot.class.php');
//include_once('classes/LivePlotModel.class.php');


//Set path to live log or sample data file here (See PlotDaemon::setup() for more)
define('AIS_LOG_PATH', 'AISMon.log');

//Set the URL of the API that will save the decoded data
define('API_POST_URL', getenv('MDM_CRT_PLOT_POST'));
define('API_DELETE_URL', getenv('MDM_CRT_PLOT_DELETE'));

//function to post data to a webpage using cURL
function post_page($url, $data=array('postvar1' => 'value1')) { 
    $ch = curl_init();
    //UA last updated 4/10/21
    $ua = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.114 Safari/537.36";
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 40);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    //ob_start();
    $response = curl_exec($ch);
    return $response;
  
    //ob_end_clean();
    curl_close($ch);
} 

//Function to convert stored GMT time to central time for display
function getTimeOffset() {
    $tz = new DateTimeZone("America/Chicago");
    $dt = new DateTime();
    $dt->setTimeZone($tz);
    return $dt->format("I") ? -18000 : -21600;
  }

//This is the active part of the app. It creates the daemon object then starts the loop.
$plotDaemon = new PlotDaemon();
$plotDaemon->start();

/*  The remainer of the script is disabled unless debugging  */

//$ais = new MyAIS();

// Test Single Message
$test = false;
if ($test) {
	$buf = "!AIVDM,1,1,,A,15DAB600017IlR<0e2SVCC4008Rv,0*64\r\n";
	// Important Note:
	// After receiving input from incoming serial or TCP/IP, call the process_ais_buf(...) method and pass in
	// the input from device for further processing.
	$ais->process_ais_buf($buf);
}

// Test With Large Array Of Messages - represent packets of incoming data from serial port or IP connection
if ($test) {
	$test2_a = array( "sdfdsf!AIVDM,1,1,,B,18JfEB0P007Lcq00gPAdv?v000Sa,0*21\r\n!AIVDM,1,1,,B,18Jjr@00017Kn",
		"jh0gNRtaHH00@06,0*37\r\n!AI","VDM,1,1,,B,18JTd60P017Kh<D0g405cOv00L<c,0*",
		"42\r\n",
		"!AIVDM,2,1,8,A,55RiwV02>3bLS=HJ220t<D4r0<u84j222222221?=PD?55Pf0BTjCQhD,0*73\r\n",
		"!AIVDM,2,2,8,A,3lQH888888",
		"88880,2*6A\r",
		"\n!AIVDM,2,1,9,A,569w5`02>0V090=V221@DpN0<PV222222222221EC8S@:5O`0B4jCQhD,0*11\r\n!AIVDM,2,2,9,A,3lQH88888888880,2*6B\r\n!AIVDO,1,1,",
		",A,D05GdR1MdffpuTf9H0,4*7","E\r\n!AIVDM,1,1,,A,?","8KWpp0kCm2PD00,2*6C\r\n!AIVDM,1,1,,A,?8KWpp1Cf15PD00,2*3B\r\nUIIII"
	);
	foreach ($test2_a as $test2_1) {
		// Important Note:
		// After receiving input from incoming serial or TCP/IP, call the process_ais_buf(...) method and pass in
		// the input from device for further processing.
		$ais->process_ais_buf($test2_1);
	}
}
?>