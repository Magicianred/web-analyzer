document.addEventListener("DOMContentLoaded", function () {
    // Put your code here ...
    var html = '<h1 id="performance-timing">' + performance.timing.domComplete + '</h1>';
    appendHtml(document.body, html);
});

function appendHtml(el, str) {
    var div = document.createElement('div');
    div.innerHTML = str;
    while (div.children.length > 0) {
        el.appendChild(div.children[0]);
    }
}

