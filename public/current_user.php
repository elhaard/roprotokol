<?php

$res=array ("status" => "ok");
include("../rowing/backend/inc/common.php");
include("utils.php");


$cuser=$_SERVER['PHP_AUTH_USER'];
error_log("CU=$cuser");

$s="SELECT Member.MemberId as member_id, CONCAT(Member.FirstName,' ', Member.LastName) as name, Member.Email as member_email 
    FROM Member 
    Where Member.MemberId=?
";

if ($stmt = $rodb->prepare($s)) {
    $stmt->bind_param('s',$cuser);
    if (!$stmt->execute()) {
        error_log("OOOP ".$rodb->error);
        $res['status']=$rodb->error;
        http_response_code(500);
    }
    $result= $stmt->get_result() or die("Error in stat query: " . mysqli_error($rodb));
} else {
    error_log("Prepare OOOP ".$rodb->error);
}
if ($result) {
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    dbErr($rodb,$res);
    echo json_encode($res);
}

?>