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
<script src="<?php echo $webbase?>/d3.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $webbase?>/normalize.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $webbase?>/style.css" />

<body>

<div class="debug">test</div>
<h1><?php echo htmlspecialchars($title) ?></h1>

<nav>
<span class="index"></span>
<button class="prev" type="button" disabled="disabled" onclick="left()"> prev </button>
<button class="next" type="button" onclick="right()">next </button>
<button class="all"  type="submit" onclick="document.location.href='<?php echo $webbase?>'">all</button>
</nav>

<div class="permalink"></div>

<div class="tagsearch">
<form method="get" action="">
 Add tag: <input list="MyTags" id="MyTagsInput" type="text" value="" />
  <datalist id="MyTags">
  </datalist>
</form>
  Current tags:<span id="currenttags"></span>
</div>

<div class="pics"> </div>

<footer>
  This gallery belongs to <?php echo htmlspecialchars($admin) ?>.
  <div class="copyright"> photo-tags: copyright 2011 Arun Persaud arun@nubati.net, code available at <a href="http://source.nubati.net/projects/photo-tags">source.nubati.net/projects/photo-tags</a></div>
</footer>


<script type="text/javascript" >

var pics = d3.select(".pics").append("ul");

var page=<?php echo $page ?>;
var N=<?php echo $N ?>;
var T="<?php echo $tags ?>";
var ID=<?php echo $pic ?>;
var count=0;

/* populate data list with tags*/
d3.json("<?php echo $webbase?>/getjson.php?S", function(json) {
    d3.select("#MyTags").selectAll("option").data(json[1])
      .enter().append("option").attr("value",function(d) {return d.name});
  });

/* update form to point to new link */
d3.select("input").on("keyup", function(d) {
    d3.select('form').attr("action","<?php echo $webbase?>/tag/"+document.getElementById('MyTagsInput').value.replace(" ","+"));
});

if (T!="")
  {
    var mycurrenttags = T.split(",");

    d3.select("#currenttags").select("button").remove();
    d3.select("#currenttags").selectAll("button")
      .data(mycurrenttags).enter()
      .append("button").attr("type","button").text( function(d) {return d;} );
  }
 else
  {
    d3.select("#currenttags").select("button").remove();
    d3.select("#currenttags").append("span").text( ' none');
  };

function load_content(a) {
  //  d3.select(".debug").text("T,P,N = *"+T+"* *"+a+"* *"+N+"*");

  update_page_index(a);

  if (ID>=0)
    url = "<?php echo $webbase?>/getjson.php?ID="+ID;
  else if(T!="")
    url = "<?php echo $webbase?>/getjson.php?T="+T+"&P="+a;
  else
    url = "<?php echo $webbase?>/getjson.php?P="+a;

  /* update pics */
  d3.json(url, function(json) {
      count=0;
      pics.selectAll("li").remove();
      picdata=json;

      /* if ID is set, just show one pictures, else create an array of pictures */
      if (ID>=0)
	{
	  pics.selectAll("li").data(picdata)
	    .enter().append("li")
	    .append("img")
	    .attr("class","large")
	    .attr("src",function(d) {
		s= d.base_uri+'/'+d.filename;
		s = s.replace('file:\/\/<?php echo "".str_replace("/","\/",$dbprefix); ?>','<?php echo $webbase?>/Photos-small/');
		return s;
	      });
	}
      else
	{
	  pics.selectAll("li").data(picdata)
	    .enter().append("li")
	    .append("a")
	    .attr("href",function(d) {
		s = '<?php echo $webbase; ?>';
		if(T!="")
		  s = s + '/tag/' + T;
		if(a!=1)
		  s = s + '/page/' + a;
		s = s + '/pic/' + d.id;
		return s;
	      })
	    .append("img")
	    .attr("src",function(d) {
		count++;
		s= d.base_uri+'/'+d.filename;
		s = s.replace('file:\/\/<?php echo "".str_replace("/","\/",$dbprefix); ?>','<?php echo $webbase?>/Photos-tiny/');
		return s;
	      });
	};

     checkbutton();
    });

  /* update permalink */

  permalink="<?php echo $webbase ?>";
  if(T!="")
    permalink += '/tag/' + T;
  if(a!=1)
    permalink += '/page/' + a;


  d3.select(".permalink").html("Permalink: <a href=\""+permalink+"\">"+permalink+"</a>");
}

function left() {
  if (page>=2) page=page-1;
  load_content(page);
}

function right() {
  page=page+1;
  load_content(page);
}

function checkbutton()
{

  if (page==1)
    { d3.select("button.prev").attr("disabled","disabled");}
  else
    { d3.select("button.prev").attr("disabled", null);};

  if (count<N)
    { d3.select("button.next").attr("disabled","disabled");}
  else
    { d3.select("button.next").attr("disabled",null);}
}

function update_page_index(mypage)
{
  /* load number of pictures */

  if(T!="")
    url = "<?php echo $webbase?>/getjson.php?C=1&T="+T;
  else
    url = "<?php echo $webbase?>/getjson.php?C=1";

  d3.json(url, function(json) {
    /* update index, show only page +-5 pages max */
    n = Math.floor(json[0].total/N);
    s = "";

    if(n>0)
      {
        s="page ";

        if(mypage>7)
	  {
	    s+=" <a href=\"<?php echo $webbase?>";
	    if(T!="")
	      s+="/tag/"+T;
	    s+="/page/1\">1</a>...";
	    start = mypage-5;
	  }
        else
	  start=1;

        for(i=start;i<=Math.min(n+1,mypage+5);i++)
	  {
	    if(i==mypage)
	      s+= " "+i+" ";
	    else
	      {
		s+=" <a href=\"<?php echo $webbase?>";
		if(T!="")
		  s+="/tag/"+T;
		s+="/page/"+i+"\">"+i+"</a>";
	      }
	  }

        if(mypage+5<n)
	  {
	    s+="... <a href=\"<?php echo $webbase?>";
	    if(T!="")
	      s+="/tag/"+T;
	    s+="/page/"+(n+1)+"\">"+(n+1)+"</a>";
	  }
        else if(mypage+5==n)
	  {
	    s+=" <a href=\"<?php echo $webbase?>";
	    if(T!="")
	      s+="/tag/"+T;
	    s+="/page/"+(n+1)+"\">"+(n+1)+"</a>";
	  };
      };
    d3.select(".index").html(s);
    } );
}

load_content(page);

</script>

</body>
</html>