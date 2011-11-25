<?php

$N=30;

/* parse ini -file */
$iniarray=parse_ini_file("config.ini");
$DBFILE=$iniarray["fspotdb"];
$usePDO=$iniarray["usePDO"];
$N=$iniarray["pics_per_page"];
/* end parse ini-file */

if (isset($_REQUEST["P"]))
  $OFFSET = "".($_REQUEST["P"]*$N-$N).",";
else
  $OFFSET = "";

if($usePDO)
  $DB = new PDO("sqlite:$DBFILE");
else
  $DB = new SQlite3($DBFILE);

$result = $DB->query("SELECT * FROM photos LIMIT $OFFSET $N");

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

foreach ($result as $res)
{
  $row[$i] = $res;
  $i++;
}


echo json_encode($row);

/* close the database */
if($usePDO)
  $DB=null;
else
  $DB->close();

?>

