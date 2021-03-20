<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}

echo "DST? = ".date("I");
?>