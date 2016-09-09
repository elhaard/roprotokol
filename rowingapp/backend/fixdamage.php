<?php
include("inc/common.php");
$data = file_get_contents("php://input");

header('Content-type: application/json');
$res = [];
$ev = '';

$fix=json_decode($data);

if (! $fix) {
    $error = 'Invalid JSON in input';
} elseif ( !$fix->action || ($fix->action != 'edit' && $fix->action != 'fix') ) {
    $error = 'action must be "edit" or "fix"';
} elseif ( !$fix->reporter ) {
    $error = 'No reporter given.';
} elseif ( !$fix->damage || !$fix->damage->id ) {
    $error = 'No damage id given.';
}

if (!isset($error)) {
   if ($stmt = $rodb->prepare("SELECT Damage.*, Boat.Name as BoatName
                               FROM Damage
                                LEFT JOIN Boat ON (Damage.Boat = Boat.id)
                               WHERE Damage.id=?")) {
      $stmt->bind_param('i', $fix->damage->id);
      $stmt->execute();
      if ( $result = $stmt->get_result() ) {
        $damage = $result->fetch_assoc();
      } else {
        $error = 'Could not find damage: ' . mysqli_error($rodb);
      }
   } else {
     $error = 'Could not find damage because of database error: ' . $rodb->error;
   }
}

if (!isset($error) && !isset($damage)) {
  $error = 'Unknown damage: ' . $fix->damage->id;
}

if (!isset($error) ){

  $rodb->query("BEGIN TRANSACTION");

  if ( $fix->action == 'fix') {

    if ($stmt = $rodb->prepare("UPDATE Damage, (SELECT id FROM Member WHERE MemberID=?) m SET Repaired=NOW(),RepairerMember=m.id, RepairComment=? WHERE Damage.id=?")) {
      $stmt->bind_param('ssi',  $fix->reporter->id,$fix->damage->comment,$fix->damage->id);
      $stmt->execute();
      $ev=$fix->reporter->name . " klarmeldte skaden: ". $damage['Description'] . " på båden ".$damage['BoatName'];
      if (isset($fix->damage->comment) && $fix->damage->comment) {
        $ev .= '. Klarmeldingskommentar: ' . $fix->damage->comment;
      }
    } else {
      $error = 'Could not fix damage because of database error';
      error_log("fix damage database error: " . $rodb->error );
    }
  } elseif ($fix->action =='edit') {
    if ($stmt = $rodb->prepare("INSERT INTO DamageHistory (Damage, ResponsibleMember, Description, Updated, Degree)
                                SELECT id, ResponsibleMember, Description, Updated, Degree
                                FROM Damage WHERE Damage.id=?")) {
       $stmt->bind_param('i',  $fix->damage->id);
       $stmt->execute();
       if ($stmt = $rodb->prepare("UPDATE Damage, (SELECT id FROM Member WHERE MemberID=?) m
                                   SET Updated = NOW(),
                                       Degree = ?,
                                       ResponsibleMember = m.id,
                                       Description = ?
                                   WHERE Damage.id=?")) {
         $stmt->bind_param('sisi',
                           $fix->reporter->id,
                           $fix->damage->degree,
                           $fix->damage->comment,
                           $fix->damage->id);
         $stmt->execute();
         $ev = $fix->reporter->name . " redigerede skaden: ".$damage['Description']. " på båden ".$damage['BoatName'];
         if ($damage['Description'] != $fix->damage->comment) {
           $ev .= '. Ny beskrivelse: ' . $fix->damage->comment;
         }
         if ($fix->damage->degree != $damage['Degree']) {
           $ev .= "\nGrad ændret fra " . $damage['Degree'] . ' til ' .$fix->damage->degree;
         }
       } else {
         $error = 'Could not save changes because of database error';
         error_log("save damage database error: " . $rodb->error );
       }
    } else {
       $error = 'Could not save old damage because of database error';
       error_log("save damage database error: " . $rodb->error );
     }
 }
 if (!isset($error) && $stmt = $rodb->prepare("INSERT INTO event_log (event,event_time) VALUES(?,NOW())")) {
    $stmt->bind_param('s', $ev);
    $stmt->execute();
  }

  $rodb->query("END TRANSACTION");
}

$rodb->close();

if (isset($error)) {
  $res['error'] = $error;
  $res['success'] = false;
} else {
  $res['success'] = true;
}

invalidate("boat");

echo json_encode($res);
?>
