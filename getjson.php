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
 else if (isset($_REQUEST["NP"]))
  {
    /* get +- 5 pics from ordered list to show next to a large image */

    /* first create a temp table with all images and then use rowid to get +-5 images */

    if (isset($_REQUEST["T"]))
      {
	/* single tag or part of tag */
	$tags = $_REQUEST["T"];
	$tags = explode(",",$tags);
	$nrtags = count($tags);

	foreach ($tags as $key => $value)
	  $tags[$key]=sqlite_escape_string(trim($value));
	$tags = "'".implode("','",$tags)."'";

	$DB->query("CREATE TEMP TABLE NEXTPREV AS SELECT base_uri, filename, p.id as id  FROM photo_tags pt, photos p, tags t".
		   " WHERE pt.tag_id = t.id".
		   " AND (t.name COLLATE NOCASE IN ($tags))".
		   " AND p.id = pt.photo_id ".
		   " GROUP BY p.id HAVING COUNT( p.id )=$nrtags");
      }
    else
      {
	$DB->query("CREATE TEMP TABLE NEXTPREV AS SELECT base_uri, filename, p.id as id FROM  photos p");
      };

    if (isset($_REQUEST["ID"]))
      {
	$ID=intval($_REQUEST["ID"]);
	$result = $DB->query("SELECT * FROM NEXTPREV".
			     " WHERE rowid > (select rowid from NEXTPREV where id=$ID) -5".
			     "   AND rowid < (select rowid from NEXTPREV where id=$ID) +5");
      }
    else
      {
	$result = $DB->query("SELECT 1 where 1=2");
      }

  }
 else if (isset($_REQUEST["ID"]) && !isset($_REQUEST["C"]))
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
	    $DB->query("CREATE TEMP TABLE TEMPPICS AS SELECT p.id as id FROM photo_tags pt, photos p, tags t".
		       " WHERE pt.tag_id = t.id".
		       " AND (t.name COLLATE NOCASE IN ($tags))".
		       " AND p.id = pt.photo_id ".
		       " GROUP BY p.id HAVING COUNT( p.id )=$nrtags");

	    if (isset($_REQUEST["ID"]))
	      {
		$ID = $_REQUEST["ID"];
		$result = $DB->query("SELECT count(*) as total, (SELECT rowid from TEMPPICS WHERE id = $ID) as row from TEMPPICS");
	      }
	    else
	      $result = $DB->query("SELECT count(*) as total, -1 as row from TEMPPICS");
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
	  {
	    $DB->query("CREATE TEMP TABLE TEMPPICS AS SELECT id from photos");

	    if (isset($_REQUEST["ID"]))
	      {
		$ID = $_REQUEST["ID"];
		$result = $DB->query("SELECT count(*) as total, (SELECT rowid FROM TEMPPICS WHERE id=$ID) as row FROM TEMPPICS");
	      }
	    else
	      $result = $DB->query("SELECT count(*) as total, -1 as row FROM TEMPPICS");
	  }
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

