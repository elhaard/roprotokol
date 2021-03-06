<?php
if (isset($_GET["quarter"])) {
    $quarter=$_GET["quarter"];
} else {
    echo "please set quarter";
    exit(1);
}
header("Content-Disposition: filename=gymnastikQ$quarter.csv");
set_include_path(get_include_path().':..');
include("inc/common.php");
header('Content-type: text/csv');
header("Pragma: no-cache");

$s=
 'SELECT team.name AS team, team_participation.dayofweek,team_participation.timeofday,classdate,team.description,
    CONCAT(FirstName," ",LastName) AS membername, Member.MemberID,KommuneKode,CprNo
  FROM Member, team_participation
  LEFT JOIN team on team_participation.team=team.name 
        AND team_participation.dayofweek=team.dayofweek
        AND team_participation.timeofday=team.timeofday
  WHERE  Member.id=team_participation.member_id  AND
        (QUARTER(classdate)=? AND 
        (YEAR(classdate)=YEAR(NOW()) AND QUARTER(NOW())>=?) OR (YEAR(classdate)=YEAR(NOW())-1 AND QUARTER(NOW())<?)) 
ORDER BY team.name,team_participation.timeofday
  ';

if ($stmt = $rodb->prepare($s)) {
    $stmt->bind_param("iii", $quarter,$quarter,$quarter);
    $stmt->execute();
     $result= $stmt->get_result() or die("Error in quarterly query: " . mysqli_error($rodb));     
     $out = fopen('php://output', 'w');
     echo "Hold, ugedag, holdstarttid,holddato, holdbeskrivelse, Medlem, Medlemsnr,KommuneKode,CprNo\n";

     while ($row = $result->fetch_assoc()) {
         fputcsv($out, $row);
     }
     fclose($out);
} else {
    error_log(mysqli_error($rodb));
}
