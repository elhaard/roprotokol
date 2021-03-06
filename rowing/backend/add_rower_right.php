<?php
include("inc/common.php");
include("inc/verify_user.php");

$error=null;
$res=array ("status" => "ok");
$data = file_get_contents("php://input");
$data=json_decode($data);


$admin="baadhal";
if (!empty($cuser)) {
    $admin=$cuser;
}

$rodb->begin_transaction();
//error_log('add rower right right '.json_encode($data));
//error_log('id='.$data->rower->id);
//error_log('right='.$data->right->member_right.":".$data->right->arg);
$arg=$data->right->arg;

if (!$arg) {
    $arg="";
}

if ($stmt = $rodb->prepare("INSERT INTO  MemberRights (member_id,MemberRight,argument,Acquired,created_by) SELECT m.id,?,?,NOW(),mc.id FROM Member m, Member mc WHERE m.MemberID=? AND mc.MemberID=? ON DUPLICATE KEY UPDATE Acquired=NOW()")) {
    $stmt->bind_param('ssss', $data->right->member_right,$arg,  $data->rower->id,$admin);
    if (!$stmt->execute()) {
        error_log('OOOPS rower right EXE: '.$rodb->error);
        $res["status"]="error";
        $res["message"]="$rodb->error";
    };
} else {
        $res["status"]="error";
        $res["message"]="prepare error";
        error_log('OOOPS rower right: '.$rodb->error);
}
$rodb->commit();
invalidate('member');
eventLog("$admin tildelte ".$data->right->member_right ." til ".$data->rower->id);
echo json_encode($res);
$rodb->close();
