/******************************************
* Snow Effect Script- By Altan d.o.o. (http://www.altan.hr/snow/index.html)
* Visit Dynamic Drive DHTML code library (http://www.dynamicdrive.com/) for full source code
* Last updated Nov 9th, 05' by DD. This notice must stay intact for use
******************************************/

//Configure below to change URL path to the snow image
var snowsrc = "/snow.gif";
var snowsrc2 = "/snow.png";
// Configure below to change number of snow to render
var no = 40;
// Configure whether snow should disappear after x seconds (0=never):
var hidesnowtime = 0;
// Configure how much snow should drop down before fading ("windowheight" or "pageheight")
var snowdistance = "windowheight";
window.image_box = 'content';

///////////Stop Config//////////////////////////////////

var ie4up = (document.all) ? 1 : 0;
var ns6up = (document.getElementById && !document.all) ? 1 : 0;

function iecompattest()
{
	return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

var dx, xp, yp;    // coordinate and position variables
var am, stx, sty;  // amplitude and step variables
var i, doc_width = 800, doc_height = 600; 

window.onload = function ()
{
	if(window.image_box)
	{
		image_box = document.getElementById(image_box);
		doc_width = image_box.offsetWidth;
		doc_height = image_box.offsetHeight;
	}
	else if (ns6up)
	{
		doc_width = self.innerWidth;
		doc_height = self.innerHeight;
	}
	else if (ie4up)
	{
		doc_width = iecompattest().clientWidth;
		doc_height = iecompattest().clientHeight;
	}


	dx = new Array();
	xp = new Array();
	yp = new Array();
	am = new Array();
	stx = new Array();
	sty = new Array();

	for (i = 0; i < no; ++ i)
	{
		dx[i] = 0;                        // set coordinate variables
		xp[i] = Math.random()*(doc_width-50);  // set position variables
		yp[i] = Math.random()*doc_height;
		am[i] = Math.random()*20;         // set amplitude variables
		stx[i] = 0.02 + Math.random()/10; // set step variables
		sty[i] = 0.7 + Math.random();     // set step variables
		var html;
		
		if (ie4up||ns6up)
		{
			/* 75% */
			if(i % 4)
			{
				size = 5 + Math.random()*0;
				html = "<div id=\"dot" + i + "\" class=\"snow\" style=\"POSITION: absolute; Z-INDEX: " + i + "; VISIBILITY: visible; TOP: 15px; LEFT: 15px;\"><img class=\"snow\" src='" + snowsrc2 + "' border=\"0\" style=\" width: " + size + "px;\"><\/div>";
			}
			/* 25% */
			else
			{
				size = 5 + Math.random()*10;
				html = "<div id=\"dot" + i + "\" class=\"snow\" style=\"POSITION: absolute; Z-INDEX: " + i + "; VISIBILITY: visible; TOP: 15px; LEFT: 15px;\"><img class=\"snow\" src='" + snowsrc + "' border=\"0\" style=\" width: " + size + "px;\"><\/div>";
			}
			if(image_box)
			{
				image_box.innerHTML += html;
			}
			else
			{
				document.write(html);
			}
		}
	}
	
	if (ie4up||ns6up)
	{ 
		snowIE_NS6();
		if (hidesnowtime>0)
			setTimeout("hidesnow()", hidesnowtime*1000)
	}
};

function snowIE_NS6()
{
	for (i = 0; i < no; ++ i)
	{
		yp[i] += sty[i];
		if (yp[i] > doc_height+20)
		{
			xp[i] = Math.random()*(doc_width-am[i]-30);
			yp[i] = -20;
			stx[i] = 0.02 + Math.random()/10;
			sty[i] = 0.7 + Math.random();
		}
		dx[i] += stx[i];
		document.getElementById("dot"+i).style.top=yp[i]+"px";
		document.getElementById("dot"+i).style.left=xp[i] + am[i]*Math.sin(dx[i])+"px";  
	}
	snowtimer=setTimeout("snowIE_NS6()", 10);
}

function hidesnow()
{
	if (window.snowtimer)
		clearTimeout(snowtimer)
	for (i=0; i<no; i++)
		document.getElementById("dot"+i).style.visibility="hidden"
}