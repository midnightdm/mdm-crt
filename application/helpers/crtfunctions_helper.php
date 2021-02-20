<?php 
/* * * * * 
 * application/helpers/crtfunctions_helper.php
 */

function is_selected($title, $test) {
  if($title===$test) {
    return "selected";
  } else {
    return "";
  }
}

function base_url() {
  return "localhost/mdm-crt/";
}

function getTimeOffset() {
  return date("I") ?  -18000 : -21600;
}

function getNow($dateString="Y-m-d H:i:s") {  
  return date($dateString, (time()+getTimeOffset()));
}

function getYesterdayRange() {
  $offset = -0;
  $today = getdate();
  $todayMidnight = mktime(0,0,0,$today['mon'],$today['mday'])+$offset;
  $yesterdayMidnight = $todayMidnight - 86400 +$offset;
  return [$yesterdayMidnight, ($todayMidnight-1)];
}

function getTodayRange() {
  $offset = getTimeOffset(); //-18000;
  $today = getdate();
  $todayMidnight = mktime(0,0,0,$today['mon'], $today['mday']);
  return [$todayMidnight, ($today[0]+$offset)];
}

function getLast24HoursRange() {
  $offset = getTimeOffset(); //-18000;
  $today = getdate();
  return [($today[0]-86400+$offset), ($today[0]+$offset)];
}

function getLast7DaysRange() {
  $offset = getTimeOffset(); //-18000;
  $today = getdate();
  return [($today[0]-604800+$offset), $today[0]+$offset];
}

function printRange($dateArr) {
  if(!is_array($dateArr)) {
    return "Invalid range array used in printRange()";
  }
  return "Range is ".date('g:ia l, M j', $dateArr[0])." to ".date('g:ia l, M j', $dateArr[1]);
}

