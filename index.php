<?php
/* parse ini -file */
$iniarray=parse_ini_file("config.ini");
$webbase=$iniarray["webbase"];
$dbprefix=$iniarray["dbprefix"];
$admin=$iniarray["admin"];
$title=$iniarray["title"];
$N=$iniarray["pics_per_page"];
/* end parse ini-file */

/* parse flags */
if(isset($_REQUEST["page"]))
  $page = intval($_REQUEST["page"]);
else
  $page = 1;

if(isset($_REQUEST["tag"]))
  $tags = htmlentities($_REQUEST["tag"]);
else
  $tags = "";

if(isset($_REQUEST["pic"]))
  $pic = intval(htmlentities($_REQUEST["pic"]));
else
  $pic = -1;
/* end parse flags */

/* The basic layout */
?>

<html>
<title><?php echo htmlspecialchars($title) ?></title>
<script src = "<?php echo $webbase?>/js/d3.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $webbase?>/css/normalize.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $webbase?>/css/style.css" />

<body>

<div class="debug"></div>
<h1><?php echo htmlspecialchars($title) ?></h1>

<nav>
page <span class="index"></span>
<button class="prev" type="button" disabled="disabled" onclick="prev_page()"> prev </button>
<button class="next" type="button" onclick="next_page()">next </button>
<button class="all"  type="submit" onclick="document.location.href='<?php echo $webbase?>'">all</button>
</nav>

<div class="permalink"></div>

<div class="tagsearch">
<form method="get" action="">
 Search for tag: <input list="MyTags" id="MyTagsInput" type="text" value="" />
  <datalist id="MyTags">
  </datalist>
</form>
  Current tags:<span id="currenttags"></span>
  <button class="next" type="button" onclick="tagcloud()">tag cloud</button>
</div>

<div class="nextprev"> <ul></ul></div>

<div class="pics"><ul></ul> </div>

<footer>
  This gallery belongs to <?php echo htmlspecialchars($admin) ?>.
  <div class="copyright"> photo-tags: copyright 2011 Arun Persaud arun@nubati.net, code available at <a href="http://source.nubati.net/projects/photo-tags">source.nubati.net/projects/photo-tags</a></div>
</footer>


<script src = "<?php echo $webbase?>/js/photo-tags.js"></script>
<script type="text/javascript" >
/*hand parameters over to javascript*/
var page=<?php echo $page ?>;
var N=<?php echo $N ?>;
var T="<?php echo $tags ?>";
var ID=<?php echo $pic ?>;
var count=0;
var dbprefix="<?php echo $dbprefix ?>".replace(/\//g,"\/");
var webbase = "<?php echo $webbase?>";

load_content();
update_page_index();

</script>

</body>
</html>