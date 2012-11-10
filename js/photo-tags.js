var pics = d3.select(".pics").select("ul");
var page;
var maxpage;

function init()
{
  /* populate data list with tags*/
  d3.json(webbase+"/getjson.php?S", function(json) {
      d3.select("#MyTags").selectAll("option").data(json)
        .enter().append("option").attr("value",function(d) {return d.name});
    });

  /* update form to point to new link */
  d3.select("input").on("keyup", function(d) {
      d3.select('form').attr("action",webbase+"/tag/"+document.getElementById('MyTagsInput').value.replace(" ","+"));
  });

  d3.select("#currenttags").select("button").remove();
  if (T!="")
    {
      var mycurrenttags = T.split(",");

      d3.select("#currenttags").selectAll("button")
        .data(mycurrenttags).enter()
        .append("button").attr("class","btn btn-small").text( function(d) {return d;} );
    };
}

function load_content() {
  // d3.select(".debug").text("T,P,N = *"+T+"* *"+page+"* *"+N+"*");

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
	  singlepicspace.append("img").attr("class","left").attr("src",webbase+"/icons/left.png");
	  singlepicspace.append("img")
	    .attr("class","large")
	    .attr("src",function(d) {
		s = d.base_uri+'/'+d.filename;
		s = s.replace('file:\/\/'+dbprefix,webbase+'/Photos-small/');
		return s;
	      });
	  singlepicspace.append("img").attr("class","right").attr("src",webbase+"/icons/right.png");

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
		s = s.replace('file:\/\/'+dbprefix,webbase+'/Photos-tiny/');
		return s;
	      });
	};
      checkbutton();
      update_permalink()
    });
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
    if (page>1) page=page-1;
    load_content();
    update_page_index();
}

function next_page() {
    if (page<maxpage) page=page+1;
    load_content();
    update_page_index();
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
	.style("font-size", function(d){return (Math.log(d.count+1)/3.0+0.5)+"em"})
	.text(function(d) { return d.name+" "; })
	.on("mouseover", function(d){ d3.select(this).style("color","red")} )
	.on("mouseout", function(d){ d3.select(this).style("color","black")} )
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

      thumbs.exit().select("img").transition().duration(1000).style("height","0px").transition().duration(1050).remove();
      thumbs.exit().transition().duration(1050).remove();
      thumbs.enter().append("li")
	.append("a")
	.on("click", function(d) {
	    load_pic(d.id); }
	  )
	.append("img")
	.attr("src",function(d) {
	    s= d.base_uri+'/'+d.filename;
	    s = s.replace('file:\/\/'+dbprefix,webbase+'/Photos-tiny/');
	    return s;
	  })
	.style("height","0px")
	.transition().duration(500)
	.style("height","100px");

      /* resort elements */
      d3.select(".nextprev").select("ul").selectAll("li").sort(function(a,b){return a.id-b.id;});
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
    { d3.select(".pagination ul li:first-child").classed("disabled", true);}
  else
    { d3.select(".pagination ul li:first-child").classed("disabled", false);};

  if (page==maxpage)
    { d3.select(".pagination ul li:last-child").classed("disabled", true);}
  else
    { d3.select(".pagination ul li:last-child").classed("disabled", false);};
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
      n  = Math.floor( json[0].total/N   ); /* how many pages */
      nr = Math.floor( (json[0].row-1)/N ); /* which row are we in? rowid starts at 1 not 0 */

      maxpage=n+1;

      if(nr > 0) page = nr+1;

      var mydata = new Array();  // add json data  {page: <nr>, name: <name>} ; at end reform array into real json and use d3 to parse it

      if(n>0)
      {
	  mydata.push('{ page:0.1, name:"Prev"}');
          if(page>4)
	  {
	      mydata.push('{ page:1, name:"1"}');
	      mydata.push('{ page:1.5, name:"..."}');
	      start = page-3;
	  }
          else
	      start=1;

          for(i=start;i<=Math.min(n+1,page+3);i++)
	      mydata.push('{ page:'+i+', name:"'+i+'"}');

          if(page+3<n)
	  {
	      mydata.push('{ page:'+(n+0.5)+', name:"..."}');
	      mydata.push('{ page:'+(n+1)+', name:"'+(n+1)+'"}');
	  }
          else if(page+3==n)
	      mydata.push('{ page:'+(n+1)+', name:"'+(n+1)+'"}');

	  mydata.push('{ page:'+(n+2.1)+', name:"Next"}');
      }
      else
      {
	  mydata.push('{ page:0.1, name:"Prev"}');
	  mydata.push('{ page:1.1, name:"Next"}');
      }

      mydata = "["+mydata.join(",")+"]";
      mydata =  eval('(' + mydata + ')');

      /* remove old elements */
      d3.selectAll(".pagination").select("ul").selectAll("li").remove();

      /* create new ones */
      var pageindex = d3.selectAll(".pagination").select("ul").selectAll("li").data(mydata, function(d){return d.page});
      pageindex.selectAll("li").data(mydata, function(d){ return d.page; });
      pageindex.enter().append("li").append("a")
	  .on("click", function(d) { if(  (d.page - Math.floor(d.page)) ==0 )  {page=d.page; ID=-1;load_content(); update_page_index();} })
	  .text(function(d) {return " "+d.name+" "});

      pageindex.sort( function(a,b) { return a.page- b.page;} );

      /* add callbacks to prev and next buttons */
      d3.selectAll(".pagination ul li:first-child a").on("click", function(){prev_page();});
      d3.selectAll(".pagination ul li:last-child  a").on("click", function(){next_page();});

      d3.select(".pagination").select("ul").selectAll("li").classed("active", false);
      d3.select(".pagination").select("ul").selectAll("li").classed("active", function(d) {return ( d.page == page ); });
      checkbutton();
    } );

}
