<?php
include("../../rowing/backend/inc/common.php");
dbfetch($rodb,"event_category",['*'],["priority"]);
