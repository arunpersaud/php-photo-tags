<?php
/* parse ini -file */
$iniarray=parse_ini_file("config.ini");
$webbase=$iniarray["webbase"];
$dbprefix=$iniarray["dbprefix"];
$admin=$iniarray["admin"];
$title=$iniarray["title"];
/* end parse ini-file */
?>
<html>
<title><?php echo htmlspecialchars($title) ?></title>
<script src="d3.min.js"></script>
<link rel="stylesheet" type="text/css" href="normalize.css" />
<link rel="stylesheet" type="text/css" href="style.css" />

<body>

<div class="debug">test</div>
<h1><?php echo htmlspecialchars($title) ?></h1>

<button class="prev" disabled="disabled" onclick="left()"> prev </button>
<button class="next"   onclick="right()">next </button>

<div class="pics"> </div>

<footer>
  This gallery belongs to <?php echo htmlspecialchars($admin) ?>.
  <div class="copyright"> code: copyright 2011 Arun Persaud arun@nubati.net, code available at nubati.net/git/f-spot-gallery</div>
</footer>


<script type="text/javascript" >


var pics = d3.select(".pics").append("ul");

var offset=0;
var N=30;
var count=0;

function myreload(a,b) {
  d3.json("<?php echo $webbase?>/getjson.php?O="+a+"&N="+b, function(json) {
      count=0;
      pics.selectAll("li").remove();
      pics.selectAll("li").data(json)
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

  d3.select(".debug").text("O, N= "+a+" "+b+" "+count);
}

function left() {
  if (offset>=N) offset=offset-N;
  myreload(offset,N);
}

function right() {
  offset=offset+N;
  myreload(offset,N);
}

function checkbutton() {

  if (offset==0)
    { d3.select("button.prev").attr("disabled","disabled");}
  else
    { d3.select("button.prev").attr("disabled", null);};

  if (count<N)
    { d3.select("button.next").attr("disabled","disabled");}
  else
    { d3.select("button.next").attr("disabled",null);}
}

myreload(offset,N);

</script>

</body>
</html>