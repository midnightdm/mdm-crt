<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *  daemon2/classes/LivePlot.class.php 
 * 
 *  This class is the data model for holding plain vessel tranponder data
 *  after it is decoded, but before it is further processed by LiveScan
 *  for display.
 * 
 */
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
