(function ( $ ) {
    'use strict';
    
    $('a#omise-download-promptpay-qr').click(function(e) {
        if (isCanvasSupported()) {
            e.preventDefault();
            let svg = document.getElementById('omise-promptpay-qrcode-svg');

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
        const elem = document.createElement('canvas');
        return !!(elem.getContext && elem.getContext('2d'));
    }

    function copyStylesInline(destinationNode, sourceNode) {
        let containerElements = ["svg","g"];
        for (let cd = 0; cd < destinationNode.childNodes.length; cd++) {
            let child = destinationNode.childNodes[cd];
            if (containerElements.indexOf(child.tagName) != -1) {
                copyStylesInline(child, sourceNode.childNodes[cd]);
                continue;
            }
            let style = sourceNode.childNodes[cd].currentStyle;
            if (style == "undefined" || style == null) continue;
            for (let st = 0; st < style.length; st++){
                child.style.setProperty(style[st], style.getPropertyValue(style[st]));
            }
        }
    }

    function triggerDownload (imgURI, fileName) {
        let evt = new MouseEvent("click", {
            view: window,
            bubbles: false,
            cancelable: true
        });
        let a = document.createElement("a");
        a.setAttribute("download", fileName);
        a.setAttribute("href", imgURI);
        a.setAttribute("target", '_blank');
        a.dispatchEvent(evt);
    }
    
    function downloadSvg(svg, fileName, toTriggerDownload) {
        let copy = svg.cloneNode(true);
        copyStylesInline(copy, svg);
        let data = (new XMLSerializer()).serializeToString(copy);
        const url = "data:image/svg+xml;utf8," + encodeURIComponent(data);
        let img = new Image();
        img.src = url;

        img.onload = function () {
            let canvas = document.createElement("canvas");
            let bbox = svg.getBBox();
            canvas.width = bbox.width;
            canvas.height = bbox.height;
            let ctx = canvas.getContext("2d");
            ctx.clearRect(0, 0, bbox.width, bbox.height);
            ctx.drawImage(img, 0, 0);

            if (typeof navigator !== "undefined" && navigator.msSaveOrOpenBlob) {
                let blob = canvas.msToBlob();         
                navigator.msSaveOrOpenBlob(blob, fileName);
            } else {
                let imgURI = canvas.toDataURL();
                fetch(imgURI).then(res => res.blob()).then((res) => {
                    imgURI = window.URL.createObjectURL(res)
                    if (toTriggerDownload) {
                        triggerDownload(imgURI, fileName);
                    }
                })
            }
        };
    }
}
)(jQuery);
