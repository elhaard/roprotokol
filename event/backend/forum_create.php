<?php
include("../../rowing/backend/inc/common.php");
include("inc/forummail.php");


$res=array ("status" => "ok");
$data = file_get_contents("php://input");
$newforum=json_decode($data);
$message='';
error_log(print_r($newforum,true));
if (isset($_SERVER['PHP_AUTH_USER'])) {
    $cuser=$_SERVER['PHP_AUTH_USER'];
}
// $cuser="7854"; // FIXME

if ($stmt = $rodb->prepare("INSERT INTO forum (name,description) VALUE (?,?)")) {

    $triptype="NULL";
    $stmt->bind_param(
        'ss',
        $newforum->name,
        $newforum->description
    ) ||  die("create forum BIND errro ".mysqli_error($rodb));

    if (!$stmt->execute()) {
        $error=" forum exe error ".mysqli_error($rodb);
        error_log($error);
        $message=$message."\n"."create forum insert error: ".mysqli_error($rodb);
    } 
} else {
    $error=" forum db error ".mysqli_error($rodb);
    error_log($error);
}
    
if ($error) {
    error_log($error);
    $res['message']=$message;
    $res['status']='error';
    $res['error']=$error;
}
invalidate("event");
echo json_encode($res);
?> 