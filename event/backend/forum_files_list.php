<?php
include("../../rowing/backend/inc/common.php");
dbfetch($rodb,"forum_file",['filename','folder','forum','expire','mime_type','member_from','length(file) as file_length'],["folder,filename"]);