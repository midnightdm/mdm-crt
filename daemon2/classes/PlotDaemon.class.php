<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *  
 *  daemon2/PlotDaemon.class.php
 * 
 *  This class is a daemon that runs an endless while loop, listens
 *  for raw NMEA data, decodes useful AIS information and stores it 
 *  as LivePlot objects in an array. 
 *  
 *  setup() is a substitute for __construct. Instantiate then run start().
 *
 */

class PlotDaemon {
    public $livePlot;
    public $rowsNow;
    public $rowsBefore;
    protected $run;
    protected $source;
    public $destination;
    protected $kmlpath;
    protected $lastCleanUp;

    protected function setup() {
        $this->livePlot = array();
        $this->rowsBefore = 0;
        /* This app can read AIS log files or process packets from a UDP stream. 
         *
         * For the LOGFILE option:
         *   - Configure Output Options of your AISMon program to save a log file.
         *   - Type the path to that file on line 12 of plotserver.php.
         *   - Enable $this->source = 'log' below.
         *   - Comment out $this->source = 'udp' below.
         * 
         * For the UDP stream option:
         *   - Configure Output Options of your AISMon for UDP Output (IP:Port = 127.0.0.1:10110)
         *      HINT: AISMon version 2.2.0 has no config file, so
         *            you'll have to add settings upon each start. 
         *            See classes/AISMonSS.png for screenshot.
         *   - Enable $this->source = 'udp' below.
         *   - Comment out $this->source = 'log' below.
         */
        //$this->source = 'log';
        $this->source = 'udp';

        /* Also this app can save processed data by posting to an api or saving as a kml file
         * 
         * For the API file option:
         *   - Enable $this->destination = 'api' below.
         *   - Comment out $this->destination = 'kml' below.
         *   - Define API_POST_URL path line 15 of plotserver.php.
         *   - The path in $this->kmlpath below will be ignored.
         *   
         * 
         * For the KML file option:
         *   - Enable $this->destination = 'kml' below.
         *   - Comment out $this->destination = 'api' below.
         *   - Edit a file path to $this->kmlpath = 'path/filename.kml' below that.
         *   - The API_POST_URL on line 15 of plotserver.php will be ignored.
         */
        //$this->destination = 'api';
        $this->destination = 'kml';
        $this->kmlpath = 'E:\xampp\htdocs\mdm-crt\js\pp_google.kml';
        $this->lastCleanUp = time(); //Used to increment cleanup routine

    }

    public function start() {
        echo "\t\t >>>     Type CTRL+C at any time to quit.    <<<\r\nPlotDaemon::start() \r\n";
        $this->setup();
        $this->run = true;
        if($this->destination=='api') {
            $this->removeOldPlots(true); //Initialize db
        } elseif($this->destination=='kml') {
            echo "Initialized to run in 'kml' mode using path ".$this->kmlpath."\r\n";
        }
        $this->run();
    }

    protected function run() {
        $ais = new MyAIS($this);
        
        /* UDP live port version starts here */
        if($this->source == 'udp') {
            //Reduce errors
            error_reporting(~E_WARNING);
            //Create a UDP socket
            if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))) {
                $errorcode = socket_last_error();
                $errormsg = socket_strerror($errorcode);
                die("Couldn't create socket: [$errorcode] $errormsg \n");
            }
            echo "Socket created \n";
            // Bind the source address
            if( !socket_bind($sock, "127.0.0.1", 10110) ) {
                $errorcode = socket_last_error();
                $errormsg = socket_strerror($errorcode);
                die("Could not bind socket : [$errorcode] $errormsg \n");
            }
            echo "Socket bind OK \n";
            
            while($this->run==true) {
                //** This is Main Loop this server for the UDP version ** 
                //Do some communication, this loop can handle multiple clients
                echo "Waiting for data ... \n";
                //Receive some data
                $r = socket_recvfrom($sock, $buf, 512, 0, $remote_ip, $remote_port);
                echo "$remote_ip : $remote_port -- " . $buf;
                //Send back the data to the decoder
                $ais->process_ais_buf($buf);

                //Remove old plots every 3 minutes if using api
                $isCleanUpTime = (time() - $this->lastCleanUp) > 180;
                if($this->destination=='api' && $isCleanUpTime ) {
                    $this->removeOldPlots(); 
                }

            }
            socket_close($sock);
        
         /* LOG file reading version starts here*/   
        } elseif($this->source == 'log') {
            while($this->run==true) {
                //** This is Main Loop of this server for the LOG version ** 
                // Add each read line to an array
                $file = file_get_contents(AIS_LOG_PATH); 
                if ($file) {
                    $array = explode(PHP_EOL, $file);
                    $this->rowsNow = count($array); //Saves last read line number so later resume won't duplicate
                    echo "Rows now: ".$this->rowsNow. ". Rows before: ".$this->rowsBefore.".\r\n";
                    if($this->rowsNow == $this->rowsBefore) { //Retry later if no new lines
                        sleep(5);
                        continue;
                    }
                }
                foreach($array as $element) {
                    //echo $element."\r\n";
                    $ais->process_ais_buf($element."\r\n"); //Linefeed is needed by filter in decoder
                }
                //Remove old plots every 3 minutes if using api
                $isCleanUpTime = (time() - $this->lastCleanUp) > 180;
                if($this->destination=='api' && $isCleanUpTime ) {
                    $this->removeOldPlots(); 
                }                               
                $this->rowsBefore = $this->rowsNow;
                sleep(10);
            }

        } else {
            exit("ERROR: PlotDaemon::setup() doesn't have a correct source option set.");
        }
    }

    public function saveKml() {
        $kml = "";
        $head = <<<_END
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://earth.google.com/kml/2.0">
<Document>
<Style id="mystile1r">
<LineStyle>
<color>ff0000ff</color>
</LineStyle>
<PolyStyle>
<color>7f0000ff</color>
</PolyStyle>
</Style>
<Style id="mystyle1y">
<LineStyle>
<color>ff00ffff</color>
</LineStyle>
<PolyStyle>
<color>7f00ffff</color>
</PolyStyle>
</Style>
<Style id="mystyle1b">
<LineStyle>
<color>ffff0000</color>
</LineStyle>
<PolyStyle>
<color>7fff0000</color>
</PolyStyle>
</Style>
<Style id="mystyle2">
<IconStyle id="1">
<Icon>
<href>root://icons/palette-4.png</href>
<x>32</x>
<y>0</y>
<w>32</w>
</Icon>
</IconStyle>
</Style>
_END;

        //Add head to kml string
        $kml .= $head;
        $now = time();
        //Add placemarks to kml string
        if(count($this->livePlot)>0 ) {
            foreach($this->livePlot as $key => $obj) {
                if(!is_object($obj)) {
                    echo "o is not an object\r\n";
                    continue;
                }
                //Remove when no data for 3 min
                if(($now-$obj->ts)>180) {
                    echo "Removing old $key\r\n";
                    unset($this->livePlot[$key]);
                }
                $pm = "\r\n<Placemark>\n<description>";
                $pm .= "Name ".$obj->name."\r\n";
                $pm .= "MMSI ".$obj->id."\r\n";
                $pm .= "c/s ---\r\n";
                $pm .= "IMO 0000000\r\n";
                $pm .= "Dest ---\r\n";
                $pm .= "Eta ---\r\n";
                $pm .= "Pos ".$obj->lat." ".$obj->lon."\r\n";
                $pm .= "Speed ".$obj->speed."\r\n";
                $pm .= "Course ".$obj->course."\r\n";
                $pm .= "Heading ---\r\n";
                $pm .= "Length ---\r\n";
                $pm .= "Width ---\r\n";
                $pm .= "Draft ---\r\n";
                $pm .= "Time ".$obj->ts."\r\n";
                $pm .= "</description>\r\n<name>".$obj->name."</name>\r\n";
                $pm .= "<styleUrl>#mystyle2</styleUrl>\r\n";
                $pm .= "<visibility>1</visibility>\r\n";
                $pm .= "<Point>\r\n";
                $pm .= "<altitudeMode>absolute</altitudeMode>\r\n";
                $pm .= "<coordinates>".$obj->lon.", ".$obj->lat.",0.0</coordinates>\r\n";
                $pm .= "</Point>\r\n</Placemark>\r\n";
                $kml .= $pm;
            }
        }
        
        //Add foot to kml string
        $foot = "</Document></kml>";
        $kml .= $foot;
        
        $res = file_put_contents($this->kmlpath, $kml, LOCK_EX);
        if($res) {
            echo "Saved ".$res." bytes to ".$this->kmlpath."\r\n";
        } else {
            echo "ERROR: file_put_contents failed.\r\n";
        }        
    }
    
    public function removeOldPlots($initialize=false) {
        $apiKey = getenv('MDM_CRT_DB_PWD');
        if(count($this->livePlot)>0 ) {
            $now = time();           
            foreach($this->livePlot as $key => $obj) {
                if(!is_object($obj)) {
                    echo "o is not an object\r\n";
                    continue;
                }
                //Remove when no data for 3 min
                if(($now-$obj->ts)>180) {
                    echo ">>>            Removing old $key\r\n";
                    $data = array(
                        'apiKey' => $apiKey,
                        'postType' => 'delete', 
                        'plotID' => $obj->plotID
                    );
                    $response = json_decode( post_page(API_POST_URL, $data) );
                    if($response->code==200) {
                        unset($this->livePlot[$key]); 
                    }
                    echo "Deleted ".$response->message."\r\n";
                }
            }          
        } elseif($initialize) {
            echo "Initializing by deleting all saved plots from db. ";
            $data = array(
                'apiKey' => $apiKey,
                'postType' => 'delete'
            );
            $response = json_decode( post_page(API_DELETE_URL, $data) );
            if(is_object($response)) {
                echo "Delete was ".$response->message."\r\n";
            } else {
                echo "Response: $response\r\n";
            }
            
        }
        $this->lastCleanUp = time();
    }
}
