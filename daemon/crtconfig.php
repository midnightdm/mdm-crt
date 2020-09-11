<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}
/* * * * * * *
 * Configuration file use by both Dbh and CRTdaemon classes
 * crtconfig.php
 *
 */

//'https://drive.google.com/file/d/13Q8BrhH_wo2ThUWYU6iVAfrSFxwm8P0O/google_ships.kml'
//'http://173.16.65.69:8185/pp_google.kml'
//'https://winkel-storage-app.web.app/data.kml'

//See file 'secret.txt'
$arr = [
  'kmlUrl'  => getEnv('MDM_CRT_KML_URL'),
  'timeout' => 1800,
  'errEmail'=> getEnv('MDM_CRT_ERR_EML'),
  'dbHost'  => getEnv('MDM_CRT_DB_HOST'),
  'dbUser'  => getEnv('MDM_CRT_DB_USR'),
  'dbPwd'   => getEnv('MDM_CRT_DB_PWD'),
  'dbName'  => getEnv('MDM_CRT_DB_NAME'),
  'vesselIDFilter' => [
    '003660690',
    993683111,
    993683112,
    993683113,
    993683108,
    993683109,
    993683110,
    993683155,
    993683156,
    993683157,
    993683158   
  ]
];

return $arr;
