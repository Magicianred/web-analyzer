var page = require('webpage').create();   
var system = require('system');
var nowTime = Date.now();

page.open('http://localhost:5000/measure.html', function (status) {
    console.log(window.performance.timing.domComplete - window.performance.timing.domLoading);
    console.log('Loading time:' + (Date.now() - nowTime));
    phantom.exit();
});

