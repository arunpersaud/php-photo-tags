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
    $tag = sqlite_escape_string($_REQUEST["S"]);

    $result = $DB->query("SELECT name FROM tags");
    $count = $DB->query("SELECT 1");
  }
else
  {
    if (isset($_REQUEST["P"]))
      $OFFSET = "".(intval($_REQUEST["P"])*$N-$N);
    else
      $OFFSET = "0";

    if (isset($_REQUEST["T"]))
      {
	/* single tag or part of tag */
	$tags = $_REQUEST["T"];
	$tags = explode(",",$tags);
	$nrtags = count($tags);
	foreach ($tags as $key => $value)
	  $tags[$key]=sqlite_escape_string(trim($value));
	$tags = "'".implode("','",$tags)."'";

	/* individual tags are seperated by ',' */

	/* use and AND query between tags as a default
	 a good explanation on different ways of doing this can be found at:
	 http://www.pui.ch/phred/archives/2005/04/tags-database-schemas.html
	*/
	$result = $DB->query("SELECT base_uri, filename  FROM photo_tags pt, photos p, tags t".
			     " WHERE pt.tag_id = t.id".
			     " AND (t.name COLLATE NOCASE IN ($tags))".
			     " AND p.id = pt.photo_id ".
			     " GROUP BY p.id HAVING COUNT( p.id )=$nrtags".
			     "    LIMIT $OFFSET, $N");

	$count = $DB->query("SELECT count(*) as total  FROM photo_tags pt, photos p, tags t".
			     " WHERE pt.tag_id = t.id".
			     " AND (t.name COLLATE NOCASE IN ($tags))".
			     " AND p.id = pt.photo_id ".
			     " GROUP BY p.id HAVING COUNT( p.id )=$nrtags".
			     "    LIMIT $OFFSET, $N");
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

