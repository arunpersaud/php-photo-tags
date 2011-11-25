<?php

$N=30;

/* parse ini -file */
$iniarray=parse_ini_file("config.ini");
$DBFILE=$iniarray["fspotdb"];
$usePDO=$iniarray["usePDO"];
$N=$iniarray["pics_per_page"];
/* end parse ini-file */

if($usePDO)
  $DB = new PDO("sqlite:$DBFILE");
else
  $DB = new SQlite3($DBFILE);


/* do database query */
if (isset($_REQUEST["P"]))
  {
    $OFFSET = "".($_REQUEST["P"]*$N-$N);

    $result = $DB->query("SELECT * FROM photos LIMIT $OFFSET, $N");
  }
else if (isset($_REQUEST["T"]))
  {
    $result = $DB->query("SELECT count(*) as total FROM photos");
  }
else
  $result=null;

/* encode result as an array */
$tmp=array();
if(!$usePDO)
  {
    /* convert results into array */
    while($res = $result->fetchArray(SQLITE3_ASSOC))
      {
	$tmp[]=$res;
      }
  }
else
  {
    foreach($result as $res)
      {
        $tmp[]=$res;
      }
  }
$result=$tmp;

echo json_encode($result);

/* close the database */
if($usePDO)
  $DB=null;
else
  $DB->close();


?>

