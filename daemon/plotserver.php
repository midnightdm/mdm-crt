<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}

include_once('classes\ais.2.php');

//Set path to live log file here
define('AIS_LOG_PATH', 'E:\Documents\text\AISMon.log');


//function to post to page using cURL
function post_page($url, $data=array('postvar1' => 'value1')) { 
    $ch = curl_init();
    //UA last updated 4/10/21
    $ua = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.114 Safari/537.36";
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 40);
    curl_setopt($ch, CURLOPT_URL, $url.$query);
  
    
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    //ob_start();
    $response = curl_exec($ch);
    echo $response;
    return;
    //ob_end_clean();
    curl_close($ch);
} 

function getTimeOffset() {
    $tz = new DateTimeZone("America/Chicago");
    $dt = new DateTime();
    $dt->setTimeZone($tz);
    return $dt->format("I") ? -18000 : -21600;
  }


class MyAIS extends AIS {
    public $plotDaemon;

    public function __construct($callBack=null) {
        $this->plotDaemon = $callBack;
    }
	// This function is Overridable and is called by process_ais_itu(...) method
	function decode_ais($_aisdata, $_aux) {
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
		//echo "ro: :".var_dump($ro); // dump results here for demo purpose
        //Put ro data into LivePlot object
        if(is_object($ro)) {
            $id  = $ro->mmsi;
            $key  = 'mmsi'.$id;
            $name = $ro->name;
            $speed =$ro->sog;
            $lat   = $ro->lat;
            $lon   = $ro->lon;
            $course = $ro->cog;
            $ts   = $ro->ts;
            $dest = "";

            if(isset($this->plotDaemon->livePlot[$key])) {
                //Update only if data is new
                if($lat != $this->plotDaemon->livePlot[$key]->lat || $lon != $this->plotDaemon->livePlot[$key]->lon) {
                    $this->plotDaemon->livePlot[$key]->update($ts, $name, $lat, $lon, $speed, $course);
                    echo "livePlot[$key]->update(".date("F j, Y, g:i:s a", ($ts+getTimeOffset())).", ".$name
                      .", ".$lat.", ".$lon.", ".$speed.", ".$course.")\r\n";
                }  
            } else {
                //Skip river marker numbers
                if($id < 990000000) {
                    $this->plotDaemon->livePlot[$key] = new LivePlot($ts, $name, $id, $lat, $lon, $speed, $course, $this);
                    echo "NEW livePlot[$key] (".date("F j, Y, g:i a", ($ts+getTimeOffset())).", ".$name.", ".$id.", ".$lat.", ".$lon.", ".$speed.", ".$course.")\r\n";
                } 
            }
        }

		return $ro;
	}
}

class LivePlot {

    public $ts;
    public $id;
    public $name;
    public $lat;
    public $lon;
    public $speed;
    public $course;
    protected $plotDaemon;

    public function __construct($ts, $name, $id, $lat, $lon, $speed, $course, $callBack) {
        $this->ts = $ts;
        $this->name = $name;
        $this->last = $lat;
        $this->lon  = $lon;
        $this->speed = $speed;
        $this->course = $course;
        $this->plotDaemon = $callBack;
    }

    public function update($ts, $name, $lat, $lon, $speed, $course) {
        $this->ts = $ts;
        $this->name = $name;
        $this->last = $lat;
        $this->lon  = $lon;
        $this->speed = $speed;
        $this->course = $course;
    }
}

class PlotDaemon {
    public $livePlot;
    public $rowsNow;
    public $rowsBefore;
    protected $run;

    protected function setup() {
        $this->livePlot = array();
        $this->rowsBefore = 0;
    }

    public function start() {
        echo "PlotServer::start()\n";
        $this->setup();
        $this->run = true;
        $this->run();
    }

    protected function run() {
        $ais = new MyAIS($this);

        /* UDP live port version starts here */

        //Reduce errors
        error_reporting(~E_WARNING);

        //Create a UDP socket
        if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            
            die("Couldn't create socket: [$errorcode] $errormsg \n");
        }

        echo "Socket created \n";

        // Bind the source address
        if( !socket_bind($sock, "127.0.0.1", 10110) )
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            
            die("Could not bind socket : [$errorcode] $errormsg \n");
        }

        echo "Socket bind OK \n";
        
        while($this->run==true) {
            //** This is Main Loop of this server ** 
           
           
            /* File read version starts here 
            // Add each line to an array
            $file = file_get_contents(AIS_LOG_PATH); //AIS_LOG_PATH is is set on line 7 of this script
            if ($file) {
                $array = explode(PHP_EOL, $file);
                $this->rowsNow = count($array);
                echo "Rows now: ".$this->rowsNow. ". Rows before: ".$this->rowsBefore.".\r\n";
                if($this->rowsNow == $this->rowsBefore) {
                    sleep(5);
                    continue;
                }
            }
            foreach($array as $element) {
                //echo $element."\r\n";
                $ais->process_ais_buf($element."\r\n");

            }                               
            $this->rowsBefore = $this->rowsNow;
            //exit("Exit livePlot: ".var_dump($this->livePlot));
            sleep(10);
               * * * * File Read Version Ends Here ******
            */

            /* UDP live version of loop here */
            //Do some communication, this loop can handle multiple clients

            echo "Waiting for data ... \n";
            
            //Receive some data
            $r = socket_recvfrom($sock, $buf, 512, 0, $remote_ip, $remote_port);
            echo "$remote_ip : $remote_port -- " . $buf;
            
            //Send back the data to the decoder
            $ais->process_ais_buf($buf);


            
            

        }
           socket_close($sock); 
    }
              
}

$plotDaemon = new PlotDaemon();
$plotDaemon->start();

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