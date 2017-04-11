var page = require('webpage').create();
var system = require('system');
var args = system.args;
if(args.length === 1) {
  console.log('please specify the url');
} else {
  var testURL = args[1];
}

page.open(testURL, function(status) {
  console.log("Status: " + status);
  if(status === "success") {
    setTimeout(function() {
      // To unequivocally ensure that the page has finished.
      //getMetrics(page);
      phantom.exit();
    }, 2000);
  }
});

function getMetrics(page) {
    var wpt=page.evaluate(function() {
        return window.performance.timing;
    });

    var ns=wpt.navigationStart;
    var ttfb=wpt.responseStart-ns;
    var sr=wpt.domContentLoadedEventEnd-ns;
    var renderTime = wpt.domComplete - wpt.domLoading;

    console.log('TTFB:'+ttfb+' startRender:'+sr);
    console.log(renderTime);

    var metrics=[];

    for(var i=0;i<Object.keys(wpt).length;i++) {
      val={};
      val.key=Object.keys(wpt)[i];
      val.value=wpt[val.key];
      metrics.push(val);
    }
    
    metrics.sort(function(a,b) {
      return a.value-b.value;
    });
    
    metrics.forEach(function(d) {
      console.log(d.value+'\t\t'+d.key);
    });
}
