<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}


function getTimeOffset() {
    $tz = new DateTimeZone("America/Chicago");
    $dt = new DateTime();
    $dt->setTimeZone($tz);
    return $dt->format("I") ? -18000 : -21600;
}

echo "Time offset = ".getTimeOffset();
?>