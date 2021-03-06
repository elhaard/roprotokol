#!/usr/bin/php
<?php
date_default_timezone_set("Europe/Copenhagen");
$debug=false;
$debug=(count($argv)>1 && $argv[1]=="debug");
require_once 'eutils.php';

$fd = fopen("php://stdin", "r");
$email = "";
while (!feof($fd)) {
    $line = fread($fd, 1024);
    $email .= $line;
}
fclose($fd);


//echo "email: \n$email\n\n";echo "GOT EMAIL $email";

$mime = mailparse_msg_create();
$mp=mailparse_msg_parse($mime,$email);

$headers=mailparse_msg_get_part_data($mime)["headers"];

if (empty($headers["subject"]) || empty($headers["from"])) {
  exit(1);
}

$subject=sanestring($headers["subject"]);
$subject=mb_decode_mimeheader($subject);

$froms=mailparse_rfc822_parse_addresses($headers["from"]);
$tos=mailparse_rfc822_parse_addresses($headers["to"]);
if (count($froms)!=1 || empty($froms[0]["address"])) {
  echo "no from address";
  exit(1);
}


$from=$froms[0]["address"];
$to=$tos[0]["address"];
$from=filter_var($from, FILTER_SANITIZE_EMAIL);
$to=filter_var($to, FILTER_SANITIZE_EMAIL);

//echo "$subject, $from ==> $to";
//file_put_contents ("/tmp/ie.log",$email);

$sts=mailparse_msg_get_structure($mime);
foreach ($sts as $st) {
    $part=mailparse_msg_get_part($mime,$st);
    $pd=mailparse_msg_get_part_data($part);
    $ph=$pd["headers"];
    $body="";
    if ($pd["content-type"]=='text/plain') {
        $body=substr($email, $pd["starting-pos-body"], $pd["ending-pos-body"]-$pd["starting-pos-body"]);
        $body=quoted_printable_decode($body);
        break;
    } else if ($pd["content-type"]=='text/html') {
        $body=strip_tags(str_replace("<br>","\n",quoted_printable_decode(substr($email, $pd["starting-pos-body"], $pd["ending-pos-body"]-$pd["starting-pos-body"]))),"");
        $body=html_entity_decode($body);
    }

    $charset=strtoupper($pd["charset"]);
    if ($charset !="UTF-8x" && $charset !="utf-8x" &&  $charset !="ISO-8859-x1" &&  $charset !="us-asciix") {
        $encodings=mb_list_encodings() ;
        if (in_array($charset,$encodings)) {
            $body=mb_convert_encoding ($body,'UTF-8',$charset );
        } else {
            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');
        }
    }
}
$body = preg_replace('#(^\w.+:\n)?(^>.*(\n|$))+#mi', "[citat fjernet]", $body);

if ($debug) {
    echo "from $from to $to BODY=$body\n";
    mailparse_msg_free($mime);
    exit(0);
}
$localTo=explode("@",$to)[0];

$us=explode("_",$localTo);
require_once("event/backend/messagelib.php");
if (count($us==2) && $us[0]=="member") {
    $toPrivate=$us[1];
    $toPrivate=(filter_var($toPrivate, FILTER_VALIDATE_REGEXP,["options"=>["regexp"=>"/^([gk]?[0-9]+|baadhal)$/"]]));
    if (empty($toPrivate)) {
        die("invalid recipient");
    } else {
        $fromstmt = $rodb->prepare(
            'SELECT Member.id, Member.MemberID as member_id, Email as member_email, CONCAT(FirstName," ",LastName) as from_name
     FROM Member
     WHERE Member.RemoveDate IS NULL AND Member.Email=?') or die("DB Prv err $rodb->error \n");
        $fromstmt->bind_param("s",$from);
        $fromstmt->execute() or dbErr($rodb,"private email member check DB error");
        $memberPrivateResult= $fromstmt->get_result() or die("incoming private res: " . mysqli_error($rodb));
        if ($fromMember=$memberPrivateResult->fetch_assoc()) {
            $fromName=$fromMember["from_name"];
            $fromName=mb_encode_mimeheader(preg_replace('/[^-a-zA-Z0-9 æøåÆØÅ]/', '', $fromName));
            post_private_message(
                $toPrivate,$subject,$body,
                " $fromName <member_".$fromMember["member_id"]."@aftaler.danskestudentersroklub.dk>",$fromMember["member_id"]
            );
        } else {
            die("unknown member");
        }
    }
} else {
    $forum=str_replace("_"," ",$localTo); // FIXME handle this
    echo "from $from to forum=$forum\n";
    $stmt = $rodb->prepare(
        'SELECT Member.id, Member.MemberID as member_id, forum.email_local as forum_email
     FROM forum,forum_subscription, Member
     WHERE forum.name=forum_subscription.forum AND Member.id=forum_subscription.member AND RemoveDate IS NULL AND Member.Email=? AND forum.email_local=?') or die("DB erR $rodb->error \n");
    $stmt->bind_param("ss",$from,$forum);
    $stmt->execute() or dbErr($rodb,"email member check DB error");
    $memberResult= $stmt->get_result() or die("incoming res: " . mysqli_error($rodb));
    if ($mr=$memberResult->fetch_assoc()) {
        echo "forum and subs exist\n";
        post_forum_message($forum,$subject,$body,$mr["member_id"],$mr["forum_email"]);
    } else {
        echo "\nFORUM/SUB Unvalid LT=$localTo\n";
    }
    echo "\nincoming to $forum DONE\n";
}
mailparse_msg_free($mime);
