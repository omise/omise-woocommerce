(function ( $ ) {
    'use strict';
    
    $('a#omise-download-promptpay-qr').click(function(e) {
        if (isCanvasSupported()) {
            e.preventDefault();
            var svg = document.querySelector('svg');

            /*
            Because of a Webkit (Safari) bug, where it won't fetch images from the SVG in time the first time around,
            we have to render the PNG twice or it will give us a PNG without the logo and QR code the first time :'(
            Similar issue: https://github.com/exupero/saveSvgAsPng/issues/223
            */
            downloadSvg(svg, "qr_code.png", false);
            setTimeout(function(){
                downloadSvg(svg, "qr_code.png", true);
            }, 10);
        }
    });

    function isCanvasSupported() {
        var elem = document.createElement('canvas');
        return !!(elem.getContext && elem.getContext('2d'));
    }

    function copyStylesInline(destinationNode, sourceNode) {
        var containerElements = ["svg","g"];
        for (var cd = 0; cd < destinationNode.childNodes.length; cd++) {
            var child = destinationNode.childNodes[cd];
            if (containerElements.indexOf(child.tagName) != -1) {
                 copyStylesInline(child, sourceNode.childNodes[cd]);
                 continue;
            }
            var style = sourceNode.childNodes[cd].currentStyle;
            if (style == "undefined" || style == null) continue;
            for (var st = 0; st < style.length; st++){
                 child.style.setProperty(style[st], style.getPropertyValue(style[st]));
            }
        }
    }
     
    function triggerDownload (imgURI, fileName) {
        var evt = new MouseEvent("click", {
            view: window,
            bubbles: false,
            cancelable: true
        });
        var a = document.createElement("a");
        a.setAttribute("download", fileName);
        a.setAttribute("href", imgURI);
        a.setAttribute("target", '_blank');
        a.dispatchEvent(evt);
    }
    
    function downloadSvg(svg, fileName, toTriggerDownload) {
        var copy = svg.cloneNode(true);
        copyStylesInline(copy, svg);
        var data = (new XMLSerializer()).serializeToString(copy);
        var url = "data:image/svg+xml;utf8," + encodeURIComponent(data);
        var img = new Image();
        img.src = url;

        img.onload = function () {
            var canvas = document.createElement("canvas");
            var bbox = svg.getBBox();
            canvas.width = bbox.width;
            canvas.height = bbox.height;
            var ctx = canvas.getContext("2d");
            ctx.clearRect(0, 0, bbox.width, bbox.height);

            ctx.drawImage(img, 0, 0);
            if (typeof navigator !== "undefined" && navigator.msSaveOrOpenBlob) {
                var blob = canvas.msToBlob();         
                navigator.msSaveOrOpenBlob(blob, fileName);
            } 
            else {
                var imgURI = canvas
                    .toDataURL("image/png")
                    .replace("image/png", "image/octet-stream");
                if (toTriggerDownload) {
                    triggerDownload(imgURI, fileName);
                }
            }
        };
    }
}
)(jQuery);
