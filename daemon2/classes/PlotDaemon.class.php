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
           
           
            /* ------------------------------------------------------------------ 
             File read version starts here 
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
               --------------------------------------------------------------------
               * * * * File Read Version Ends Here ******
            */

            /* UDP live version of loop is here */
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
