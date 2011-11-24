<?php

/* parse ini -file */
$iniarray=parse_ini_file("config.ini");
$DBFILE=$iniarray["fspotdb"];
$usePDO=$iniarray["usePDO"];
/* end parse ini-file */

if (isset($_REQUEST["O"]))
  $O = "".$_REQUEST["O"].",";
else
  $O="";

if (isset($_REQUEST["N"]))
  $N = "".$_REQUEST["N"];
else
  $N= 25;

if($usePDO)
  $DB = new PDO("sqlite:$DBFILE");
else
  $DB = new SQlite3($DBFILE);

$result = $DB->query("SELECT * FROM photos LIMIT $O $N");

$row = array();

$i = 0;

if(!$usePDO)
  {
    /* convert results into array */
    $tmp=array();
    while($res = $result->fetchArray(SQLITE3_ASSOC)){
      $tmp[]=$res;
    }
    $result=$tmp;
  }

foreach ($result as $res){

  $row[$i] = $res;
 
  $i++;
  
 }


echo json_encode($row);

/* close the database */
if($usePDO)
  $DB=null;
else
  sqlite_close($DB);

?>

