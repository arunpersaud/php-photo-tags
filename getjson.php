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
  }
 else if (isset($_REQUEST["ID"]))
  {
    $id  = intval($_REQUEST["ID"]);
    $result = $DB->query("SELECT base_uri, filename, id FROM photos".
			 " WHERE id=$id");
  }
 else if (isset($_REQUEST["CLOUD"]))
  {
    $result = $DB->query("SELECT t.name as name, count(*) as count FROM photo_tags pt ".
			 " LEFT JOIN tags t on t.id=pt.tag_id".
			 " GROUP BY t.id");
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

	if (isset($_REQUEST["C"]))
	  {
	    $result = $DB->query("SELECT count(*) as total from (SELECT p.id FROM photo_tags pt, photos p, tags t".
				" WHERE pt.tag_id = t.id".
				" AND (t.name COLLATE NOCASE IN ($tags))".
				" AND p.id = pt.photo_id ".
				" GROUP BY p.id HAVING COUNT( p.id )=$nrtags)");
	  }
	else
	  {
	    $result = $DB->query("SELECT base_uri, filename, p.id as id  FROM photo_tags pt, photos p, tags t".
				 " WHERE pt.tag_id = t.id".
				 " AND (t.name COLLATE NOCASE IN ($tags))".
				 " AND p.id = pt.photo_id ".
				 " GROUP BY p.id HAVING COUNT( p.id )=$nrtags".
				 "    LIMIT $OFFSET, $N");

	  }
      }
    else
      {
	if (isset($_REQUEST["C"]))
	  $result = $DB->query("SELECT count(*) as total FROM photos");
	else
	  $result = $DB->query("SELECT * FROM photos LIMIT $OFFSET, $N");
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

echo json_encode($result);

/* close the database */
if($usePDO)
  $DB=null;
else
  $DB->close();


?>

