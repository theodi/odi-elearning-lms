
function getUrlVars() {
	var vars = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
		vars[key] = value;
	});
	return vars;
}

function isInt(n){
    return Number(n) === n && n % 1 === 0;
}

var module = getUrlVars()["module"];
var completePie = dc.pieChart('#complete-pie');
var passedPie = dc.pieChart('#passed-pie');
var emailPie = dc.pieChart('#email-pie');
var themeLine = dc.rowChart('#theme-line');
var langLine = dc.rowChart('#lang-line');
var completeBar = dc.barChart('#complete-bar');
var timeBar = dc.barChart('#time-bar');

d3.csv('https://odi-elearning.herokuapp.com/data.php?module='+module, function (data) {
    var ndx = crossfilter(data);
    var all = ndx.groupAll();


    var complete = ndx.dimension(function(d) {
        return d.complete;
    });
    
    var completeGroup = complete.group();
   
    completePie
        .width(160)
        .height(160)
        .radius(80)
        .dimension(complete)
        .group(completeGroup)
//        .ordinalColors(['green', 'red')
        .colors(d3.scale.ordinal().domain(["true","false"]).range(['blue','gray']))
        .label(function (d) {
            var label = d.key;
            return label;
        });
    
    var percent = ndx.dimension(function(d) {
	value = Math.round(d.completion * 10);
        return +value;
    });
   
    var percentGroup = percent.group();
   
    completeBar
        .width(360)
        .height(160)
        .dimension(percent)
        .group(percentGroup)
        .x(d3.scale.linear().domain([0,11]))
	.gap(0.1)
	.brushOn(true);

    var passed = ndx.dimension(function(d) {
        return d.passed;
    });
    
    var passedGroup = passed.group();
   
    passedPie
        .width(160)
        .height(160)
        .radius(80)
        .dimension(passed)
        .group(passedGroup)
//        .ordinalColors(['green', 'red')
        .colors(d3.scale.ordinal().domain(["true","false"]).range(['blue','gray']))
        .label(function (d) {
            var label = d.key;
            return label;
        });
    
    var email = ndx.dimension(function(d) {
        return d.email;
    });
    
    var emailGroup = email.group();
   
    emailPie
        .width(160)
        .height(160)
        .radius(80)
        .dimension(email)
        .group(emailGroup)
//        .ordinalColors(['green', 'red')
        .colors(d3.scale.ordinal().domain(["true","false"]).range(['blue','gray']))
        .label(function (d) {
            var label = d.key;
            return label;
        });
    
    var theme = ndx.dimension(function(d) {
        return d.theme;
    });
    
    var themeGroup = theme.group();
   
    themeLine
        .width(320)
        .height(160)
        .dimension(theme)
        .group(themeGroup)
        .label(function (d) {
            var label = d.key;
            return label;
        });
    
    var lang = ndx.dimension(function(d) {
        return d.lang;
    });
    
    var langGroup = lang.group();
   
    langLine
        .width(320)
        .height(160)
        .dimension(lang)
        .group(langGroup)
//        .ordinalColors(['green', 'red')
//        .colors(d3.scale.ordinal().domain(["true","false"]).range(['blue','gray']))
        .label(function (d) {
            var label = d.key;
            return label;
        });
    
    var time = ndx.dimension(function(d) {
	raw = d.session_time;
	hours = parseInt(raw.substring(0,4));
	mins = parseInt(raw.substring(5,7));
	secs = parseInt(raw.substring(9,11));
	extra = Math.round(secs / 60);
	total = (hours * 60) + mins + extra;
	if (!isInt(total)) {
		total = 0;
	}
	if (total > 10) {
		total = 10;
	}
        return +total;
    });
   
    var timeGroup = time.group();
   
    timeBar
        .width(360)
        .height(160)
        .dimension(time)
        .group(timeGroup)
        .x(d3.scale.linear().domain([0,11]))
	.gap(0.1)
	.brushOn(true);
 
    var count = function() {
	number = complete.top(Number.POSITIVE_INFINITY).length;
	document.getElementById('total').innerHTML = number;
    }
	setInterval(function() {console.log(count());},1000); 

        dc.renderAll();
    });
    
