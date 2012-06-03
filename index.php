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
<script src = "<?php echo $webbase?>/d3.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $webbase?>/normalize.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $webbase?>/style.css" />

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


<script type="text/javascript" >

var page=<?php echo $page ?>;
var N=<?php echo $N ?>;
var T="<?php echo $tags ?>";
var ID=<?php echo $pic ?>;
var count=0;

var webbase = "<?php echo $webbase?>";
var pics = d3.select(".pics").select("ul");


/* populate data list with tags*/
d3.json(webbase+"/getjson.php?S", function(json) {
    d3.select("#MyTags").selectAll("option").data(json)
      .enter().append("option").attr("value",function(d) {return d.name});
  });

/* update form to point to new link */
d3.select("input").on("keyup", function(d) {
    d3.select('form').attr("action",webbase+"/tag/"+document.getElementById('MyTagsInput').value.replace(" ","+"));
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

function load_content() {
  //  d3.select(".debug").text("T,P,N = *"+T+"* *"+page+"* *"+N+"*");

  if (ID>=0)
    url = webbase+"/getjson.php?ID="+ID;
  else if(T!="")
    url = webbase+"/getjson.php?T="+T+"&P="+page;
  else
    url = webbase+"/getjson.php?P="+page;

  /* update pics */
  d3.json(url, function(json) {
      count=0;
      pics.selectAll("li").remove();
      picdata=json;

      /* if ID is set, just show one pictures, else create an array of pictures */
      if (ID>=0)
	{
	  var singlepicspace=pics.selectAll("li").data(picdata, function(d){return ID;}).enter().append("li").append("div").attr("class","singlepic");
	  singlepicspace.append("div").attr("class","left").append("img").attr("src",webbase+"/icons/left.png");
	  singlepicspace.append("img")
	    .attr("class","large")
	    .attr("src",function(d) {
		s= d.base_uri+'/'+d.filename;
		s = s.replace('file:\/\/<?php echo "".str_replace("/","\/",$dbprefix); ?>',webbase+'/Photos-small/');
		return s;
	      });
	  singlepicspace.append("div").attr("class","right").append("img").attr("src",webbase+"/icons/right.png");

	  update_thumbnails();
	}
      else
	{
	  d3.select(".nextprev").select("ul").selectAll("li").remove();
	  pics.selectAll("li").data(picdata)
	    .enter().append("li")
	    .append("a")
	    .on("click", function(d) { load_pic(d.id); })
	    .append("img")
	    .attr("src",function(d) {
		count++;
		s= d.base_uri+'/'+d.filename;
		s = s.replace('file:\/\/<?php echo "".str_replace("/","\/",$dbprefix); ?>',webbase+'/Photos-tiny/');
		return s;
	      });
	};

     checkbutton();
    });

   update_permalink()
}

function update_permalink() {
  /* update permalink */

  permalink = webbase;

  if(T!="")
    permalink += '/tag/' + T;
  if(page!=1)
    permalink += '/page/' + page;
  if(ID>0)
    permalink += '/pic/' + ID;

  d3.select(".permalink").html("Permalink: <a href=\""+permalink+"\">"+permalink+"</a>");
}

function prev_page() {
  if (page>=2) page=page-1;
  load_content();
}

function next_page() {
  page=page+1;
  load_content();
}

function prev_pic() {
}

function next_pic() {
}

function load_pic(myid) {
  ID=myid;
  update_page_index();
  update_thumbnails();
  load_content();
}

function tagcloud() {

  url = webbase+"/getjson.php?CLOUD=1";

  pics.selectAll("li").remove();

  var svgelement=pics.append("li")
    .append("svg").attr("width",400).attr("height",400);

  /* update pics */
  d3.json(url, function(json) {
      svgelement.selectAll("text").data(json).enter().append("text")
	.style("font-size", function(d){return (Math.log(d.count+1)/2.0)+"em"})
	.text(function(d) { return d.name+" "; })
	.on("mouseover", function(d){ d3.select(this).style("color","red")} )
	.on("mouseout", function(d){ d3.select(this).style("color","white")} )
	.on("click", function(d) { document.location.href=webbase+'/tag/'+d.name })
    });
}

function update_thumbnails(){
  if(T!="")
    url2 = webbase+"/getjson.php?NP=1&T="+T+"&ID="+ID;
  else
    url2 = webbase+"/getjson.php?NP=1&ID="+ID;

  var IDprev=-1;
  var IDnext=-1;
  var IDcurr=-1;
  d3.json(url2, function(json2) {
      /* figure out where the arrows on the pic should link to */
      all=""
      for (var i in json2){
	if( IDcurr != ID )
	  {
	    IDprev = IDcurr;
	    IDcurr = IDnext;
	    IDnext = json2[i].id;
	  };
      }

      var thumbs= d3.select(".nextprev").select("ul").selectAll("li").data(json2, function(d) {return d.id;});

      thumbs.enter().append("li")
	.append("a")
	.on("click", function(d) {
	    load_pic(d.id); }
	  )
	.append("img")
	.attr("src",function(d) {
	    s= d.base_uri+'/'+d.filename;
	    s = s.replace('file:\/\/<?php echo "".str_replace("/","\/",$dbprefix); ?>',webbase+'/Photos-tiny/');
	    return s;
	  })
	.style("height","0")
	.transition().duration(1000)
	.style("height","100px");

      thumbs.exit().select("img").transition().duration(1000).style("height","0");
      thumbs.exit().transition().duration(1050).remove();

      /* resort elements */
      d3.select(".nextprev").select("ul").selectAll("li").sort(function(a,b){return a.id-b.id;});
      d3.select(".nextprev").select("ul").selectAll("li").select("a").select("img").classed("current",false);
      d3.select(".nextprev").select("ul").selectAll("li").select("a").select("img").classed("current",function(d){return (d.id==IDcurr);});


      /* add links for left/right arrows */
      if (IDprev != -1 )
	d3.select(".left").on("click", function() { load_pic(IDprev); });
      if (IDnext != -1 )
	d3.select(".right").on("click", function() { load_pic(IDnext); });

    });
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

function update_page_index()
{
  /* load number of pictures */
  myID = "";
  if(ID > 0)
    myID = "&ID="+ID;

  if(T!="")
    url = webbase+"/getjson.php?C=1&T="+T+myID;
  else
    url = webbase+"/getjson.php?C=1"+myID;

  d3.json(url, function(json) {
    /* update index, show only page +-5 pages max */
    n = Math.floor(json[0].total/N);
    nr = Math.floor( (json[0].row-1)/N); /* rowid starts at 1 not 0 */

    if(nr > 0)
      page = nr+1;

    var mydata = new Array();  // add json data  {page: <nr>, name: <name>} ; at end reform array into real json and use d3 to parse it

    if(n>0)
      {
        if(page>7)
	  {
	    mydata.push('{ page:1, name:"1"}');
	    mydata.push('{ page:1.5, name:"..."}');
	    start = page-5;
	  }
        else
	  start=1;

        for(i=start;i<=Math.min(n+1,page+5);i++)
	  mydata.push('{ page:'+i+', name:"'+i+'"}');

        if(page+5<n)
	  {
	    mydata.push('{ page:'+(n+0.5)+', name:"..."}');
	    mydata.push('{ page:'+(n+1)+', name:"'+(n+1)+'"}');
	  }
        else if(page+5==n)
	  mydata.push('{ page:'+(n+1)+', name:"'+(n+1)+'"}');
      };
    mydata = "["+mydata.join(",")+"]";
    mydata =  eval('(' + mydata + ')');

    var pageindex = d3.select(".index").selectAll("button").data(mydata, function(d){ return d.page; });
    pageindex.exit().remove();
    pageindex.enter().append("button")
      .on("click", function(d) { if(  (d.page - Math.floor(d.page)) ==0 )  {page=d.page; ID=-1;load_content(); update_page_index();} })
      .text(function(d) {return " "+d.name+" "});
    pageindex.sort( function(a,b) { return a.page- b.page;} );
    } );

  d3.select(".index").selectAll("button").classed("currentpage",false);
  d3.select(".index").selectAll("button").classed("currentpage",function(d){return (d.page==page);});
}

load_content();
update_page_index();

</script>

</body>
</html>