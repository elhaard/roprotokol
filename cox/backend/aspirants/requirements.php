<?php

include("../../../rowing/backend/inc/common.php");
$s='SELECT name,description,expiry,dispensation
    FROM course_requirement
    ORDER BY name
';
$result=$rodb->query($s) or die("Error in stat query: " . mysqli_error($rodb));
echo '[';
 $first=1;
 while ($row = $result->fetch_assoc()) {
	  if ($first) $first=0; else echo ',';	  
	  echo json_encode($row);
}
echo ']';
?> 
