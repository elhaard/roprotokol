<?php
include("../../rowing/backend/inc/common.php");
include("utils.php");
header('Content-type: application/json');

$s="SELECT JSON_MERGE(
    JSON_OBJECT(
      'id',Member.MemberID, 
      'name', CONCAT(FirstName,' ',LastName) 
   ),
   CONCAT(
  '{', JSON_QUOTE('rights'),': [',
     GROUP_CONCAT(JSON_OBJECT(
      'member_right',MemberRight,'arg',argument,'acquired',Acquired)),
   ']}')
   ) AS json
   FROM Member LEFT JOIN MemberRights on MemberRights.member_id=Member.id  
   WHERE Member.MemberID!='0'
   GROUP BY Member.id";

# Member.MemberID should not be necessary, non members should have MemberID=NULL, not 0

if ($sqldebug) {
    echo $s."<br>\n";
}
$result=$rodb->query($s) or die("Error in stat query: " . mysqli_error($rodb));;
echo '[';
 $first=1;
 while ($row = $result->fetch_assoc()) {
	  if ($first) $first=0; else echo ",\n";
	  echo $row['json'];
}
echo ']';
$rodb->close();
?> 
