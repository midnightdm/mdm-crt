<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}
/* * * * * * *
 * Configuration file use by both Dbh and CRTdaemon classes
 * crtconfig.php
 *
 */

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
    3660692,
    '003660690',
    '003660692',
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
