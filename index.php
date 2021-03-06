<?php

  /**
    copyright 2012,2013,2017 Arun Persaud <arun@nubati.net>

    This file is part of Php-photo-tags.

    Php-photo-tags is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Php-hoto-tags is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Php-photo-tags.  If not, see <http://www.gnu.org/licenses/>.

  **/

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

/* autoversioning of js and css files */
function autoversion($file)
{
  /* changes the file name of e.g. css/style.css to css/style.<md5>.css/js
   * this way the browser can cache the file and will reload it if the file changed
   * needs to have .htaccess set up correctly to link back to css/style.css */

  /* only use it for file that have an absolut path */
  if(!file_exists(dirname($_SERVER['SCRIPT_FILENAME']). '/' . $file))
    return $file;

  $md5 = md5_file(dirname($_SERVER['SCRIPT_FILENAME']). '/' . $file);
  return preg_replace('{\\.([^./]+)$}', ".$md5.\$1", $file);
}

/* The basic layout */
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo htmlspecialchars($title) ?></title>
<script src = "<?php echo $webbase.autoversion("/js/d3.min.js")?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $webbase.autoversion("/css/bootstrap.min.css")?>" />
<link rel="stylesheet" type="text/css" href="<?php echo $webbase.autoversion("/css/style.css")?>" />
</head>

<body>

<div class="debug"></div>
<h1><?php echo htmlspecialchars($title) ?></h1>

<nav>

<div class="pagination">
  <ul>
  </ul>
</div>

</nav>


<div class="permalink"></div>

<div class="tagsearch">
<form class="form-search" method="get" action="">
  <p>
    <label>Search for tag:</label> <input class="input-medium search-query" list="MyTags" id="MyTagsInput" type="text" value="" />
    <datalist id="MyTags">
    </datalist>
    Current tags:<span id="currenttags"></span>
    <a class="next btn btn-small btn-info" onclick="tagcloud()">tag cloud</a>
    <a class="btn btn-small btn-success" href='<?php echo $webbase?>'>all</a>
    <span id="pictags"></span>
  </p>
</form>
</div>

<div class="nextprev"> <ul></ul></div>

<div class="pics"><ul></ul> </div>

<footer>
  <div class="pull-left">This gallery belongs to <?php echo htmlspecialchars($admin) ?>.</div>
  <div class="copyright pull-right"> php-photo-tags: copyright 2011,2012,2017 Arun Persaud arun@nubati.net,<br /> code available at <a href="https://github.com/arunpersaud/php-photo-tags">php-photo-tags@github</a></div>
</footer>


<script src = "<?php echo $webbase.autoversion("/js/photo-tags.js")?>"></script>
<script src="http://code.jquery.com/jquery-latest.js"></script>
<script src="js/bootstrap.min.js"></script>
<script type="text/javascript" >

/*hand parameters over to javascript*/
var page=<?php echo $page ?>;
var N=<?php echo $N ?>;
var T="<?php echo $tags ?>";
var ID=<?php echo $pic ?>;
var count=0;
var dbprefix="<?php echo $dbprefix ?>".replace(/\//g,"\/");
var webbase = "<?php echo $webbase?>";

init();
load_content();
update_page_index();

</script>

</body>
</html>