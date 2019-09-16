<?php
include("inc/common.php");
include("inc/utils.php");
header('Content-type: application/json');

$s="SELECT Destination.Location as location, Destination.Name as name,Destination.Name as orig_name, Meter as distance, ExpectedDurationNormal AS duration, ExpectedDurationInstruction AS duration_instruction
   FROM Destination
   ORDER BY Location,name";

// echo $s;
$stmt = $rodb->prepare($s) or dbErr($rodb,$res,"prep");
$stmt->execute();
$result= $stmt->get_result() or dbErr($rodb,$res,"Error in destinations query: ");
$d=[];
output_rows($result);
$rodb->close();
