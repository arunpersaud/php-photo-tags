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
if (isset($_REQUEST["S"]))
  {
    /* single tag or part of tag */
    $tag = $_REQUEST["S"];
    /* individual tags are separated by '+' */
    $result = $DB->query("SELECT name FROM tags where name like \"%$tag%\"");
    $count = $DB->query("SELECT 1");
  }
else
  {
    if (isset($_REQUEST["P"]))
      $OFFSET = "".($_REQUEST["P"]*$N-$N);
    else
      $OFFSET = "0";

    if (isset($_REQUEST["T"]))
      {
	/* single tag or part of tag */
	$tags = $_REQUEST["T"];
	$tags = explode("+",$tags);
	$tags = "'".implode("','",$tags)."'";

	/* individual tags are seperated by '+' */
	$result = $DB->query("SELECT base_uri, filename FROM photos ".
			     "    left join photo_tags on photos.id=photo_tags.photo_id ".
			     "    left join tags on tags.id=photo_tags.tag_id ".
			     "    where tags.name in ($tags) LIMIT $OFFSET, $N");

	$count = $DB->query("SELECT count(*) as total FROM photos ".
			    "    left join photo_tags on photos.id=photo_tags.photo_id ".
			    "    left join tags on tags.id=photo_tags.tag_id ".
			    "    where tags.name in ($tags)");

      }
    else
      {
	$result = $DB->query("SELECT * FROM photos LIMIT $OFFSET, $N");
	$count = $DB->query("SELECT count(*) as total FROM photos");
      }
  }

/* encode result as an array */
$tmp=array();
if(!$usePDO)
  {
    /* convert results into array */
    while($res = $result->fetchArray(SQLITE3_ASSOC))
      $tmp[]=$res;
  }
else
  {
    foreach($result as $res)
      $tmp[]=$res;
  }
$result=$tmp;

/* encode count as an array */
$tmp=array();
if(!$usePDO)
  {
    /* convert results into array */
    while($res = $count->fetchArray(SQLITE3_ASSOC))
      $tmp[]=$res;
  }
else
  {
    foreach($count as $res)
      $tmp[]=$res;
  }
$count=$tmp;

$return=array($count,$result);

echo json_encode($return);

/* close the database */
if($usePDO)
  $DB=null;
else
  $DB->close();


?>

