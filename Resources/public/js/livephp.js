/**
 * Live.php
 * @author Bence Meszaros
 * @link http://bencemeszaros.com
 * @version 1.0
 */

var LivePhp = {

    interval: 1000,
    url: '',
    start: 0,
    errors: 0,
    errorLimit: 10,
    currentLinkElements: {},

    /** Initializes the start time and the query cicle */
    init: function() {
        // the url for the monitor (with the front controller)
        LivePhp.url = '/app_dev.php/livephpmonitor';

        if (0 === LivePhp.start) {
            LivePhp.start = new Date() * 1;
            setTimeout(LivePhp.heartbeat, LivePhp.interval);
        }
    },

    /** Reload all local css files */
    reloadCss: function() {
        // helper method to check if a given url is local
        function isLocal(url) {
            var loc = document.location,
                reg = new RegExp("^\\.|^\/(?!\/)|^[\\w]((?!://).)*$|" + loc.protocol + "//" + loc.host);
            return url.match(reg);
        }

        var links = document.getElementsByTagName("link");

        for (var i = 0; i < links.length; i++) {
            var link = links[i], rel = link.getAttribute("rel"), href = link.getAttribute("href");
            if (href && rel && rel.match(new RegExp("stylesheet", "i")) && isLocal(href)) {
                // remove any url params
                var res = href.match(/(.*)\?.*/);
                href = res && res[1] ? res[1] : href;
                LivePhp.currentLinkElements[href] = link;
            }
        }

        for (var url in LivePhp.currentLinkElements) {
            var head = LivePhp.currentLinkElements[url].parentNode,
                newLink = document.createElement("link"),
                oldLink = LivePhp.currentLinkElements[url];

            newLink.setAttribute("href", url + "?timestamp=" + new Date() * 1);
            newLink.setAttribute("rel", "stylesheet");
            newLink.addEventListener('load', function() {
                setTimeout((function(node){return function() {
                    node.parentNode.removeChild(node);
                };})(oldLink), 100);
            }(oldLink), false);
            head.appendChild(newLink);
            LivePhp.currentLinkElements[url] = newLink;
        }
    },

    scriptSource: function(scripts) {
        var scripts = document.getElementsByTagName('script'),
            script = scripts[scripts.length - 1];

        if (script.getAttribute.length !== undefined) {
            return script.src;
        }

        return script.getAttribute('src', -1);
    },

    /** performs a cycle per interval */
    heartbeat: function() {
        if (document.body) {
            LivePhp.ask();
        }
    },

    /** Queries the server for changes, and reloads the page on positive answer */
    ask: function() {
        var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XmlHttp");
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                var restart = new Date() * 1;
                if (xhr.responseText != '' && xhr.status == 200) {
                    var re = JSON.parse(xhr.responseText);

                    if (re === true) {
                        location.reload();
                        return true;
                    }
                    else {
                        // we got a number, meaning we should reload all local css files
                        restart = typeof re == "number" ? re : restart;
                        LivePhp.reloadCss();
                    }
                }
                // Error, or no response at all
                else if (xhr.status >= 400 || xhr.status == 0) {
                    // After 10 errors we stop asking
                    LivePhp.errors ++;
                    if (LivePhp.errors >= LivePhp.errorLimit) {
                        return false;
                    }
                }
                // reset the start time
                LivePhp.start = restart;
                setTimeout(LivePhp.heartbeat, LivePhp.interval);
            }
        };
        xhr.open("GET", LivePhp.url + '/' + LivePhp.start, true);
        xhr.send();
    }

};

LivePhp.init();