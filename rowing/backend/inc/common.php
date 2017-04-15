<?php
header('Content-type: application/json');
ini_set('default_charset', 'utf-8');
ini_set('display_errors', 'Off');
error_reporting(E_ALL);

define( 'ROOT_DIR', dirname(__FILE__) );
set_include_path('.:..:'.ROOT_DIR);



$skiplogin=false;

if(!isset($_SESSION)){
  session_start();
}
if (isset($_GET["season"])) {
    $season=$_GET["season"];
} else {
  $season=date('Y');
}

$sqldebug=false;
if (isset($_GET["sqldebug"])) {
    $sqldebug=true;
}

require_once("db.php");
if (!$rodb->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $rodb->error);
}

function mysdate($jsdate) {
    $r=preg_replace("/\.\d\d\dZ/","",$jsdate);
    return($r);
}

function invalidate($tp) {
    $mem  = new Memcached();
    $mem->setOption(Memcached::OPT_BINARY_PROTOCOL, TRUE);
    $mem->addServer('127.0.0.1',11211);
    $mem->increment($tp, 1, time());

}

function dbErr(&$db, &$res, $err="") {
    $res["status"]=$db->error;
    error_log("Database error " . $db->error);
    http_response_code(500);
}


?>