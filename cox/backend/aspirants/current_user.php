<?php

$res=array ("status" => "ok");

set_include_path(get_include_path().':/');
include("../../../rowing/backend/inc/common.php");
include("utils.php");


$cuser=$_SERVER['PHP_AUTH_USER'];
error_log("CU $cuser");

$s="SELECT MemberRight as member_right,Member.MemberId as member_id, wish, team_requests.phone,team_requests.email,team,
    CONCAT(Member.FirstName,' ', Member.LastName) as name, preferred_time, preferred_intensity,
    activities, comment, Member.Email as member_email, team_requests.email as request_email, team_requests.phone as phone,
    team_requests.wish,team_requests.preferred_intensity,team_requests.comment,team_requests.activities,team_requests.preferred_time
    FROM Member LEFT JOIN team_requests 
          on Member.id=team_requests.member_id
          LEFT JOIN MemberRights ON MemberRights.member_id=Member.id AND MemberRight='cox'
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
error_log("CU2");

if ($result) {
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    dbErr($rodb,$res);
    echo json_encode($res);
}


?>