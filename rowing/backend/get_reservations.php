<?php
require("inc/common.php");
include("inc/utils.php");

$s="
SELECT Boat.id as boat_id,Boat.Name as boat, TIME_FORMAT(start_time,'%H:%i') as start_time,start_date,TIME_FORMAT(end_time,'%H:%i') AS end_time,end_date,
    dayofweek,reservation.description,TripType.Name as triptype, TripType.id as triptype_id,purpose, configuration
    FROM reservation,Boat,TripType,status
    WHERE Boat.id=reservation.boat AND TripType.id=reservation.triptype
          AND (dayofweek>0 OR end_date>=DATE(NOW())) AND
          status.reservation_configuration=reservation.configuration
    ORDER BY boat,start_date,dayofweek,start_time
";

if ($sqldebug) {
    echo $s;
}
$result=$rodb->query($s) or die("Error in event query: " . mysqli_error($rodb));;
echo '[';
 $first=1;
 while ($row = $result->fetch_assoc()) {
	  if ($first) $first=0; else echo ",\n";
	  echo json_encode($row);
}
echo ']';
$rodb->close();
