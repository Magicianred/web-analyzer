function clickBtn() {        
        var pTag = "";
        var endP = "";
        var len = 0;
        for(var index=0;index<len;index++) {
            pTag += "<p>This is text";
            endP += "</p>";
        }
        var body = document.getElementById("the-body");
        var now = Date.now();
        body.innerHTML = pTag + "This is text" + endP;
        var curr = Date.now();
        console.log("The measure time with Date.now: " + (now));
        console.log("The measure time with Date.now: " + (curr - now));
        var responseHtml = "<script>console.log(Date.now())<\/script>";
        responseHtml = responseHtml.replace(/<\/?sc[^\>]+>/g, "");
        var ele = document.createElement("script");
        ele.innerHTML = responseHtml;
        document.body.appendChild(ele);
}

