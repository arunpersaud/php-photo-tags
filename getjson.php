<?php

/* parse ini -file */
$iniarray=parse_ini_file("config.ini");
$DBFILE=$iniarray["fspotdb"];
/* end parse ini-file */

if (isset($_REQUEST["O"]))
  $O = "".$_REQUEST["O"].",";
else
  $O="";

if (isset($_REQUEST["N"]))
  $N = "".$_REQUEST["N"];
else
  $N= 25;

$DB = new SQlite3($DBFILE);

$result = $DB->query("SELECT * FROM photos LIMIT $O $N");

sqlite_close(DB);

$row = array();

$i = 0;

while($res = $result->fetchArray(SQLITE3_ASSOC)){
    
  $row[$i] = $res;
 
  $i++;
  
 }


echo json_encode($row);

?>

