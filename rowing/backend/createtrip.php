<?php
include("inc/common.php");

$res=array ("status" => "ok");
$data = file_get_contents("php://input");
$newtrip=json_decode($data);
$tripDescription=$newtrip->triptype->name ." til ". $newtrip->destination->name." i ".$newtrip->boat->name;

$message="createtrip  "; //.json_encode($newtrip);
$error=null;
$logevents=[];
// error_log(" Create trip $data");

$rodb->begin_transaction();

//error_log("COL BOAT=".print_r($newtrip->boat->location,true)."DD");
if ($newtrip->boat->location != "Andre") {
    if ($stmt = $rodb->prepare("SELECT 'x' FROM  Trip WHERE BoatID=? AND InTime IS NULL AND OutTime<?")) {
        $expectedTime=mysdate($newtrip->expectedtime);
        $stmt->bind_param('is', $newtrip->boat->id,$expectedTime);
        $stmt->execute();
        $result= $stmt->get_result();
        if ($result->fetch_assoc()) {
            $res["status"]="error";
            $error="already on water";
            error_log($error);
            error_log($data);
        }
    }
}

foreach ($newtrip->rowers as $rower) {
    if ($stmt = $rodb->prepare("SELECT Boat.name AS boat, Trip.Destination  FROM Trip,TripMember,Member,Boat WHERE Boat.id=Trip.BoatID AND Member.MemberID=? AND Member.id=TripMember.member_id AND TripMember.TripID=Trip.id AND Trip.InTime IS NULL AND Trip.OutTime < NOW()")) {
        $stmt->bind_param('s', $rower->id);
        $stmt->execute();
        $result= $stmt->get_result();
        if ($r=$result->fetch_assoc()) {
            $res["status"]="error";
            $error .= "$rower->name er allerede på vandet i ".$r["boat"] . " til " . $r["Destination"]."\n";
        }
    }
}


$teamName=null;

if (!empty($newtrip->trip_team)) {
    $teamName=$newtrip->trip_team->name;
}
$expectedtime=mysdate($newtrip->expectedtime);
if (!$error) {
    $starttime=mysdate($newtrip->starttime);
    // error_log('now new trip'. json_encode($newtrip));
    if ($stmt = $rodb->prepare(
        "INSERT INTO Trip(BoatID,Destination,TripTypeID,CreatedDate,EditDate,OutTime,ExpectedIn,Meter,info,Comment,team)
                VALUES(?,?,?,NOW(),NOW(),CONVERT_TZ(?,'+00:00','SYSTEM'),CONVERT_TZ(?,'+00:00','SYSTEM'),?,?,?,?)")) {
        $info="client: ".$newtrip->client_name;
        $stmt->bind_param('isississs',
        $newtrip->boat->id ,
        $newtrip->destination->name,
        $newtrip->triptype->id,
        $starttime,
        $expectedtime,
        $newtrip->distance,
        $info,
        $newtrip->comments,
        $teamName
        );
        if (!$stmt->execute()) {
            $error=mysqli_error($rodb);
            $message=$message."\n"."create trip insert error: ".mysqli_error($rodb);
        }
    } else {
        $error="trip Insert DB STMT  error: ".mysqli_error($rodb);
        error_log($error);
    }

    if ($stmt = $rodb->prepare("SELECT LAST_INSERT_ID() as tripid FROM DUAL")) {
        $stmt->execute();
        $result= $stmt->get_result() or die("Error trip id query: " . mysqli_error($rodb));
        $res['tripid']= $result->fetch_assoc()['tripid'];
    } else {
        error_log($rodb->error);
    }

    if ($stmt = $rodb->prepare(
        "INSERT INTO TripMember(TripID,Seat,member_id,CreatedDate,EditDate)
         SELECT LAST_INSERT_ID(),?,Member.id,NOW(),NOW()
           FROM Member
           WHERE MemberId=?"
    )) {
        // error_log("Create trip insert tripmembers");
        $seat=1;
        foreach ($newtrip->rowers as $rower) {
            $stmt->bind_param('is',$seat,$rower->id);
            $stmt->execute();
            $seat+=1;
        }
    } else {
        error_log("OOOPS 2 :".$rodb->error);
        $error="createtrip Member DB error: ".mysqli_error($rodb);
    }
}

if (isset($newtrip->event)) {
    if ($stmt = $rodb->prepare("INSERT INTO event_log (event,event_time) VALUES(?,NOW())")) {
        $ev= "$tripDescription : ". $newtrip->event;
        $stmt->bind_param('s', $ev);
        $stmt->execute();
    } else {
        error_log("create trip: log failed");
    }
}

if ($error) {
    error_log('Create Trip DB error ' . $error);
    $res['message']=$message.'\n'.$error;
    $res['status']='error';
    $res['error']=$error;
    $rodb->rollback();
} else {
    $rodb->commit();
}

foreach ($newtrip->rowers as $rower) {
  //error_log("check ".$rower->id);
    if ($stmt = $rodb->prepare("SELECT CONCAT(FirstName,' ',LastName) as name, MemberID, member_type From Member WHERE Member.MemberID=? AND (member_type=1 OR RemoveDate IS NOT NULL)")) {
        $stmt->bind_param('s', $rower->id);
        $stmt->execute();
        $result= $stmt->get_result();
        if ($r=$result->fetch_assoc()) {
            if (!empty($r["RemoveDate"])) {
                eventLog("$tripDescription : roer ".$r["name"] ." udmeldt " . $r["RemoveDate"]);
            }
            if (($r["member_type"]==1)) {
                eventLog("$tripDescription : roer ".$r["name"] ." er passivt medlem");
            }
        }
    } else {
        error_log(mysqli_error($rodb));
    }
}


# DSR 55.71472/12.58661

$now=time();
$iswinter=!date("I");
$sunset=date_sunset($now, SUNFUNCS_RET_TIMESTAMP, 55.71472, 12.58661, 90.5833333, 1);
$sunrise=date_sunrise($now, SUNFUNCS_RET_TIMESTAMP, 55.71472, 12.58661, 90.5833333, 1);

// error_log("SUNS winter=$iswinter, sunset=$sunset, ".date('H:i',$sunset));
if ($iswinter) {
    if ($sunset < $now or $now<$sunrise) {
        $res['notification']='det er mørkt og vinter, I skal ikke ro nu';
    } else if ($sunset < strtotime($newtrip->expectedtime)) {
        $res['notification']='Solen går ned klokken ' . date('H:i',$sunset) . '. I skal være i land kl '.date('H:i',$sunset-1800).'. Er du sikker på, at I kan nå at komme ind i tide?';
    }
} else {
    if ($sunset < $now or $now<$sunrise) {
        $res['notification']='det er mørkt, husk en lygte';
    } else if ($sunset < strtotime($newtrip->expectedtime)) {
        $res['notification']='det bliver mørkt klokken ' . date('H:i',$sunset) .' Husk en lygte';
    }
}

$res['message']=$message;
invalidate("trip");
$rodb->close();
echo json_encode($res);
