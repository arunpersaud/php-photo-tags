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
  $tags = $_REQUEST["tag"];
else
  $tags = "";

?>
<html>
<title><?php echo htmlspecialchars($title) ?></title>
<script src="<?php echo $webbase?>/d3.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $webbase?>/normalize.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $webbase?>/style.css" />

<body>

<div class="debug">test</div>
<h1><?php echo htmlspecialchars($title) ?></h1>

<button class="prev" disabled="disabled" onclick="left()"> prev </button>
<button class="next"   onclick="right()">next </button>

<div class="permalink"></div>

<div class="tagsearch">
<form method="get" action="">
 Tags: <input list="MyTags" id="MyTagsInput" type="text" value="" />
  <datalist id="MyTags">
  </datalist>
</form>
</div>

<div class="index"></div>
<div class="pics"> </div>

<footer>
  This gallery belongs to <?php echo htmlspecialchars($admin) ?>.
  <div class="copyright"> code: copyright 2011 Arun Persaud arun@nubati.net, code available at nubati.net/git/f-spot-gallery</div>
</footer>


<script type="text/javascript" >

var pics = d3.select(".pics").append("ul");

var page=<?php echo $page ?>;
var N=<?php echo $N ?>;
var T="<?php echo $tags ?>";
var count=0;

/* populate data list with tags*/
d3.json("<?php echo $webbase?>/getjson.php?S", function(json) {
    d3.select("#MyTags").selectAll("option").data(json)
      .enter().append("option").attr("value",function(d) {return d.name});
  });

/* update form to point to new link */
d3.select("input").on("keyup", function(d) {
    d3.select('form').attr("action","<?php echo $webbase?>/tag/"+document.getElementById('MyTagsInput').value);
});

function myreload(a) {
  d3.select(".debug").text("T,P,N ="+T+" "+a+" "+N);

  if(T!="")
    url = "<?php echo $webbase?>/getjson.php?T="+T+"&P="+a;
  else
    url = "<?php echo $webbase?>/getjson.php?P="+a;

  d3.json(url, function(json) {

      /* update index */
      s="page ";
      n = json[0][0].total/N;
      for(i=1;i<=n+1;i++)
	{
	  s+=" <a href=\"<?php echo $webbase?>";
	  if(T!="")
	    s+="/tag/"+T;
	  s+="/page/"+i+"\">"+i+"</a>";
	}
      d3.select(".index").html(s);

      /* update pics */
      count=0;
      pics.selectAll("li").remove();
      picdata=json[1];
      pics.selectAll("li").data(picdata)
	.enter().append("li")
	.append("a")
	.attr("href",function(d) {
	    s= d.base_uri+'/'+d.filename;
	    s = s.replace('file:\/\/<?php echo "".str_replace("/","\/",$dbprefix); ?>','<?php echo $webbase; ?>/Photos-small/');
	    return s;
	  })
	.append("img")
	.attr("src",function(d) {
	    count++;
	    s= d.base_uri+'/'+d.filename;
	    s = s.replace('file:\/\/<?php echo "".str_replace("/","\/",$dbprefix); ?>','<?php echo $webbase?>/Photos-tiny/');
	    return s;
	  });
      checkbutton();
    });

  permalink="<?php echo $webbase ?>/page/"+page;
  d3.select(".permalink").html("Permalink: <a href=\""+permalink+"\">"+permalink+"</a>");
}

function left() {
  if (page>=2) page=page-1;
  myreload(page);
}

function right() {
  page=page+1;
  myreload(page);
}

function checkbutton() {

  if (page==1)
    { d3.select("button.prev").attr("disabled","disabled");}
  else
    { d3.select("button.prev").attr("disabled", null);};

  if (count<N)
    { d3.select("button.next").attr("disabled","disabled");}
  else
    { d3.select("button.next").attr("disabled",null);}
}

myreload(page);

</script>

</body>
</html>