/*! apollo.js v1.7.0 | (c) 2014 @toddmotto | https://github.com/toddmotto/apollo */
!function(n,t){"function"==typeof define&&define.amd?define(t):"object"==typeof exports?module.exports=t:n.apollo=t()}(this,function(){"use strict";var n,t,s,e,o={},c=function(n,t){"[object Array]"!==Object.prototype.toString.call(n)&&(n=n.split(" "));for(var s=0;s<n.length;s++)t(n[s],s)};return"classList"in document.documentElement?(n=function(n,t){return n.classList.contains(t)},t=function(n,t){n.classList.add(t)},s=function(n,t){n.classList.remove(t)},e=function(n,t){n.classList.toggle(t)}):(n=function(n,t){return new RegExp("(^|\\s)"+t+"(\\s|$)").test(n.className)},t=function(t,s){n(t,s)||(t.className+=(t.className?" ":"")+s)},s=function(t,s){n(t,s)&&(t.className=t.className.replace(new RegExp("(^|\\s)*"+s+"(\\s|$)*","g"),""))},e=function(e,o){(n(e,o)?s:t)(e,o)}),o.hasClass=function(t,s){return n(t,s)},o.addClass=function(n,s){c(s,function(s){t(n,s)})},o.removeClass=function(n,t){c(t,function(t){s(n,t)})},o.toggleClass=function(n,t){c(t,function(t){e(n,t)})},o});
/**
 *
 * Responsive menu
 * A vanilla JS responsive menu plugin, by Robin Poort - Timble
 * http://robinpoort.com - http://www.timble.net
 *
 * Browser support: IE9+ (IE8 doesn't need a responsive menu since it's not responsive)
 *
 * Dependency: apollo JS | https://github.com/toddmotto/apollo
 * Plugin boilerplate by | http://gomakethings.com/mit/
 *
 * Free to use under the MIT License.
 *
 */

(function (root, factory) {
    if ( typeof define === 'function' && define.amd ) {
        define('responsivemenu', factory(root));
    } else if ( typeof exports === 'object' ) {
        module.responsivemenu = factory(root);
    } else {
        root.responsivemenu = factory(root);
    }
})(this, function (root) {

    'use strict';

    // Variables
    var exports = {}; // Object for public APIs
    var supports = !!document.querySelector && !!root.addEventListener; // Feature test
    var settings; // Plugin settings
    var menu; // The actual menu item
    var hasChildren = false;
    var subtoggles = false;

    // Default settings
    var defaults = {
        menu: '',
        initiated_class: 'rm-initiated',
        before_element: '',
        toggletype: 'button',
        toggleclass: 'rm-togglebutton',
        toggleclosedclass: 'rm-togglebutton--closed',
        togglecontent: 'menu',
        subtoggletype: 'button',
        subtoggleclass: 'rm-subtoggle',
        subtogglecontent: '+',
        sticky: 0,
        absolute: 0,
        hideclass: 'rm-closed',
        openclass: 'rm-opened',
        openbodyclass: 'has-opened-menu',
        focusedclass: 'rm-focused',
        animateopenclass: 'is-opening',
        animatecloseclass: 'is-closing',
        animateduration: 0, // (Animated with CSS so set to same duration as CSS value)
        subanimateopenclass: 'is-opening',
        subanimatecloseclass: 'is-closing',
        subanimateduration: 0, // (Animated with CSS so set to same duration as CSS value)
        parentclass: 'rm-parent',
        fullmenuclass: 'rm-fullmenu',
        absolutemenuclass: 'rm-absolutemenu',
        bodyoverflowhiddenclass: 'rm-bodyoverflowhidden',
        menuoverflowautoclass: 'rm-menuoverflowauto',
        stickyclass: 'rm-sticky',
        stickyinitiatedclass: 'rm-sticky-initiated',
        noresponsivemenuclass: 'rm-no-responsive-menu',
        mobileindicatorid: 'rm-mobile-indicator',
        mobilesubmenuindicatorid: 'rm-mobile-submenu-indicator',
        onAfterInit: function() {},
        onBeforeToggleOpen: function() {},
        onAfterToggleOpen: function() {},
        onBeforeToggleClose: function() {},
        onAfterToggleClose: function() {},
        onBeforeSubToggleOpen: function() {},
        onAfterSubToggleOpen: function() {},
        onBeforeSubToggleClose: function() {},
        onAfterSubToggleClose: function() {}
    };

    // Methods
    /**
     * A simple forEach() implementation for Arrays, Objects and NodeLists
     * @private
     * @param {Array|Object|NodeList} collection Collection of items to iterate
     * @param {Function} callback Callback function for each iteration
     * @param {Array|Object|NodeList} scope Object/NodeList/Array that forEach is iterating over (aka `this`)
     */
    var forEach = function (collection, callback, scope) {
        if (Object.prototype.toString.call(collection) === '[object Object]') {
            for (var prop in collection) {
                if (Object.prototype.hasOwnProperty.call(collection, prop)) {
                    callback.call(scope, collection[prop], prop, collection);
                }
            }
        } else {
            for (var i = 0, len = collection.length; i < len; i++) {
                callback.call(scope, collection[i], i, collection);
            }
        }
    };

    /**
     * Merge defaults with user options
     * @private
     * @param {Object} defaults Default settings
     * @param {Object} options User options
     * @returns {Object} Merged values of defaults and options
     */
    var extend = function ( defaults, options ) {
        var extended = {};
        forEach(defaults, function (value, prop) {
            extended[prop] = defaults[prop];
        });
        forEach(options, function (value, prop) {
            extended[prop] = options[prop];
        });
        return extended;
    };

    /**
     * Remove whitespace from a string
     * @private
     * @param {String} string
     * @returns {String}
     */
    var trim = function ( string ) {
        return string.replace(/^\s+|\s+$/g, '');
    };

    /**
     * Convert data-options attribute into an object of key/value pairs
     * @private
     * @param {String} options Link-specific options as a data attribute string
     * @returns {Object}
     */
    var getDataOptions = function ( options ) {
        var settings = {};
        // Create a key/value pair for each setting
        if ( options ) {
            options = options.split(';');
            options.forEach( function(option) {
                option = trim(option);
                if ( option !== '' ) {
                    option = option.split(':');
                    settings[option[0]] = trim(option[1]);
                }
            });
        }
        return settings;
    };

    /**
     * Run when window resize is done (after x ms)
     */
    var waitForFinalEvent = (function () {
        var timers = {};
        return function (callback, ms, uniqueId) {
            if (!uniqueId) {
                uniqueId = "Don't call this twice without a uniqueId";
            }
            if (timers[uniqueId]) {
                clearTimeout (timers[uniqueId]);
            }
            timers[uniqueId] = setTimeout(callback, ms);
        };
    })();

    /**
     * Get parents
     */
    function getParents(element, tag, stop) {
        var nodes = [];
        while (element.parentNode && element.parentNode != stop) {
            element = element.parentNode;
            if (element.tagName == tag) {
                nodes.push(element);
            }
        }
        return nodes
    }

    /**
     * Get style
     */
    function getStyle(el,styleProp)
    {
        var x = document.getElementById(el);

        if (window.getComputedStyle)
        {
            var y = document.defaultView.getComputedStyle(x,null).getPropertyValue(styleProp);
        }
        else if (x.currentStyle)
        {
            var y = x.currentStyle[styleProp];
        }

        return y;
    }

    // Responsive menu
    function initialize(settings) {

        menu = settings.wrapper.getElementsByTagName('ul')[0] || settings.menu;

        // Add a class when JS is initiated
        apollo.addClass(settings.wrapper, settings.initiated_class);

        // Function to run after init
        settings.onAfterInit();

        // See if menu has children
        var parents = menu.querySelectorAll('li ul');
        if ( parents.length ) {
            hasChildren = true;
            subtoggles = document.getElementsByClassName(settings.subtoggleclass);

            // Create mobile submenu width indicator
            var mobilesubmenuindicator = document.createElement('div');
            settings.wrapper.appendChild(mobilesubmenuindicator);
            mobilesubmenuindicator.id = settings.mobilesubmenuindicatorid;
            var mobilesubindicatorZindex = 0;
        }

        // Create mobile width indicator
        var mobileindicator = document.createElement('div');
        settings.wrapper.appendChild(mobileindicator);
        mobileindicator.id = settings.mobileindicatorid;
        var mobileindicatorZindex = 0;

        // Creating the main toggle button
        var toggle_element = document.createElement(settings.toggletype);
        apollo.addClass(toggle_element, [settings.toggleclass, settings.hideclass]);
        if ( settings.before_element == '' ) { settings.before_element = settings.wrapper.firstChild }
        settings.before_element.parentNode.insertBefore(toggle_element, settings.before_element);
        var togglebutton = document.getElementsByClassName(settings.toggleclass)[0];
        togglebutton.innerHTML = settings.togglecontent;
        togglebutton.setAttribute('aria-hidden', 'true');
        togglebutton.setAttribute('aria-pressed', 'false');
        togglebutton.setAttribute('type', 'button');

        // Subtoggles and parent classes
        if ( hasChildren ) {
            for (var i = 0; i < parents.length; i++) {
                var subtoggle_element = document.createElement(settings.subtoggletype);
                apollo.addClass(subtoggle_element, [settings.subtoggleclass, settings.hideclass]);
                var parent = parents[i].parentNode;
                parent.insertBefore(subtoggle_element, parent.firstChild);
                subtoggle_element.innerHTML = settings.subtogglecontent;
                subtoggle_element.setAttribute('aria-hidden', 'true');
                subtoggle_element.setAttribute('aria-pressed', 'false');
                subtoggle_element.setAttribute('type', 'button');
                apollo.addClass(parents[i].parentNode, settings.parentclass);
            }
        }

        // Adding classes
        function classes() {

            menu = settings.wrapper.getElementsByTagName('ul')[0] || settings.menu;

            mobileindicatorZindex = getStyle(settings.mobileindicatorid, "z-index");

            if ( parents.length ) {
                mobilesubmenuindicator = getStyle(settings.mobilesubmenuindicatorid, "z-index");
            }

            // If wrapper is small and if the menu is not already opened
            if ( mobileindicatorZindex == 0 && !apollo.hasClass(menu, settings.openclass) ) {

                // Show the toggle button(s)
                apollo.removeClass(togglebutton, settings.hideclass);

                // Hide the menu
                apollo.removeClass(menu, [settings.openclass, settings.fullmenuclass]);
                apollo.addClass(menu, settings.hideclass);
                apollo.removeClass(document.body, settings.openbodyclass);

                // Make the menu absolute positioned
                if ( settings.absolute == 1 ) {
                    apollo.addClass(menu, settings.absolutemenuclass);
                }

            } else if ( mobileindicatorZindex == 1 ) {

                // Hide the toggle button(s)
                apollo.addClass(togglebutton, settings.hideclass);
                apollo.removeClass(togglebutton, settings.toggleclosedclass);

                // Show the menu and remove all classes
                apollo.removeClass(menu, [settings.openclass, settings.hideclass]);
                apollo.addClass(menu, settings.fullmenuclass);
                apollo.removeClass(document.body, settings.openbodyclass);

                // Undo absolute positioning
                if ( settings.absolute == 1 && apollo.hasClass(menu, settings.absolutemenuclass) ) {
                    apollo.removeClass(menu, settings.absolutemenuclass);
                }
            }

            if ( hasChildren && mobilesubmenuindicator == 0 ) {
                forEach(subtoggles, function (value, prop) {
                    if ( !apollo.hasClass(subtoggles[prop], settings.toggleclosedclass) ) {
                        apollo.addClass(subtoggles[prop].parentNode.getElementsByTagName('ul')[0], settings.hideclass);
                        apollo.removeClass(subtoggles[prop], settings.hideclass);
                    }
                });
            } else if (hasChildren && mobilesubmenuindicator == 1) {
                forEach(subtoggles, function(value, prop) {
                    apollo.removeClass(subtoggles[prop].parentNode.getElementsByTagName('ul')[0], settings.hideclass);
                    apollo.addClass(subtoggles[prop], settings.hideclass);
                });
            }
        }

        // Sticky menu body height
        function stickyMenu() {

            menu = settings.wrapper.getElementsByTagName('ul')[0] || settings.menu;

            if ( settings.sticky == 1 ) {

                // The current menu and viewport heights
                var menuheight = settings.wrapper.offsetHeight;
                var viewportheight = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);

                // Add the overflow class but only if there is space
                if ( viewportheight <= menuheight && !apollo.hasClass(document.body, settings.bodyoverflowhiddenclass) ) {

                    apollo.addClass(document.body, settings.bodyoverflowhiddenclass);
                    apollo.addClass(settings.wrapper, settings.menuoverflowautoclass);

                } else if ( viewportheight > menuheight ) {

                    if ( apollo.hasClass(document.body, settings.bodyoverflowhiddenclass) ) {
                        apollo.removeClass(document.body, settings.bodyoverflowhiddenclass);
                        apollo.removeClass(settings.wrapper, settings.menuoverflowautoclass);
                    }

                    // Make sticky
                    if ( !apollo.hasClass(settings.wrapper, settings.stickyclass) ) {
                        apollo.addClass(settings.wrapper, settings.stickyclass);
                    }

                    // Add padding only if menu is closed or when value is stored
                    if ( !apollo.hasClass(menu, settings.openclass) && !apollo.hasClass(document.body, settings.stickyinitiatedclass) ) {

                        // Calculate the height
                        var paddingtop = menuheight.toString() + 'px';

                        // Set the padding on the body
                        document.body.setAttribute('style', 'padding-top:' + paddingtop);
                        apollo.addClass(document.body, settings.stickyinitiatedclass);
                    }
                }
            }
        }

        // Initial load
        window.addEventListener('load', function() {
            classes();
            stickyMenu();
        }, true);

        // On resize
        window.addEventListener('resize', function() {

            // Run immediately
            classes();
            stickyMenu();

            // Run again after 200 ms for safari OSX when scrollbars are visible and you're resizing to a smaller window
            waitForFinalEvent(function(){
                classes();
                stickyMenu();
            }, 200);

        }, true);

        // Accessible focus menu
        var menulinks = menu.getElementsByTagName('a');
        for (var i = 0; i < menulinks.length; i++) {
            menulinks[i].onblur = function() {
                var focusedItems = document.getElementsByClassName('rm-focused');
                for (var f = 0; f < focusedItems.length; f++) {
                    apollo.removeClass(focusedItems[f], settings.focusedclass);
                }
            };
            menulinks[i].onfocus = function() {
                // Remove the class
                var siblings = this.parentNode.parentNode.querySelectorAll('li');
                if (siblings.length) {
                    for (var f = 0; f < siblings.length; f++) {
                        apollo.removeClass(siblings[f], settings.focusedclass);
                    }
                }
                // Add the class
                var parent = getParents(this, "LI", menu);
                if (parent.length) {
                    for (var f = 0; f < parent.length; f++) {
                        apollo.addClass(parent[f], settings.focusedclass);
                    }
                }
            };
        }

        // Clicking the toggle button
        togglebutton.onclick = function() {

            menu = settings.wrapper.getElementsByTagName('ul')[0] || settings.menu;

            // Show the menu
            if ( apollo.hasClass(menu, settings.hideclass) ) {

                // Function to run before toggling
                settings.onBeforeToggleOpen();

                // Show the menu
                apollo.removeClass(menu, settings.hideclass);
                apollo.addClass(menu, settings.openclass);

                // Add class to body element you could use for styling
                apollo.addClass(document.body, settings.openbodyclass);

                // Set toggled class to toggle button
                apollo.addClass(togglebutton, settings.toggleclosedclass);

                // Set and remove animate class after duration
                apollo.addClass(menu, settings.animateopenclass);
                setTimeout(function() {

                    // Remove animation class
                    apollo.removeClass(menu, settings.animateopenclass);

                    // Function to run after toggling
                    settings.onAfterToggleOpen();

                }, settings.animateduration);
            }

            // Hide the menu
            else if ( apollo.hasClass(menu, settings.openclass) ) {

                menu = settings.wrapper.getElementsByTagName('ul')[0] || settings.menu;

                // Function to run before toggling
                settings.onBeforeToggleClose();

                // Properly set animating classes
                apollo.addClass(menu, settings.animatecloseclass);

                // Remove toggled class to toggle button
                apollo.removeClass(togglebutton, settings.toggleclosedclass);

                // When animation is done
                setTimeout(function() {

                    // Remove animate class
                    apollo.removeClass(menu, settings.animatecloseclass);

                    // Hide the menu
                    apollo.removeClass(menu, settings.openclass);
                    apollo.addClass(menu, settings.hideclass);

                    // Remove class from body element you could use for styling
                    apollo.removeClass(document.body, settings.openbodyclass);

                    // Function to run after toggling
                    settings.onAfterToggleClose();

                }, settings.animateduration);
            }

            // Check if the menu still fits
            stickyMenu();

            return false;
        };

        // Clicking the sub toggles button
        if ( hasChildren ) {

            menu = settings.wrapper.getElementsByTagName('ul')[0] || settings.menu;

            forEach(subtoggles, function(value, prop) {

                // Variables
                var subtoggle = subtoggles[prop];
                var submenu = subtoggle.parentNode.getElementsByTagName('ul')[0];

                // Click buttons and show submenu
                subtoggle.onclick = function() {

                    // Open
                    if ( apollo.hasClass(submenu, settings.hideclass) ) {

                        // Function to run before toggling
                        settings.onBeforeSubToggleOpen();

                        // Properly set animating classes
                        apollo.addClass(menu, settings.subanimateopenclass);

                        // Add class to subtoggle button
                        apollo.addClass(subtoggle, settings.toggleclosedclass);

                        // Show sub menu
                        apollo.removeClass(submenu, settings.hideclass);

                        setTimeout(function() {

                            // Remove animate class
                            apollo.removeClass(menu, settings.subanimateopenclass);

                            // Function to run before toggling
                            settings.onAfterSubToggleOpen();

                        }, settings.subanimateduration);
                    }

                    // Close
                    else if ( !apollo.hasClass(submenu, settings.hideclass) ) {

                        // Function to run before toggling
                        settings.onBeforeSubToggleClose();

                        // Properly set animating classes
                        apollo.addClass(menu, settings.subanimatecloseclass);

                        // Remove class from subtoggle button
                        apollo.removeClass(subtoggle, settings.toggleclosedclass);

                        setTimeout(function() {

                            // Remove animate class
                            apollo.removeClass(menu, settings.subanimatecloseclass);

                            // Set classes
                            apollo.addClass(submenu, settings.hideclass);

                            // Function to run before toggling
                            settings.onAfterSubToggleClose();

                        }, settings.subanimateduration);

                    }

                    // Check if the menu still fits
                    stickyMenu();
                }
            });
        }
    }

    /**
     * Initialize Plugin
     * @public
     * @param {Object} options User settings
     */
    exports.init = function ( options ) {
        // feature test
        if ( !supports ) {
            document.documentElement.className += ' ' + settings.noresponsivemenuclass;
            return;
        }
        settings = extend( defaults, options || {} ); // Merge user options with defaults
        initialize(settings);
    };

    // Public APIs
    return exports;

});
/**
 * SVGInjector v1.1.3 - Fast, caching, dynamic inline SVG DOM injection library
 * https://github.com/iconic/SVGInjector
 *
 * Copyright (c) 2014-2015 Waybury <hello@waybury.com>
 * @license MIT
 */

(function (window, document) {

    'use strict';

    // Environment
    var isLocal = window.location.protocol === 'file:';
    var hasSvgSupport = document.implementation.hasFeature('http://www.w3.org/TR/SVG11/feature#BasicStructure', '1.1');

    function uniqueClasses(list) {
        list = list.split(' ');

        var hash = {};
        var i = list.length;
        var out = [];

        while (i--) {
            if (!hash.hasOwnProperty(list[i])) {
                hash[list[i]] = 1;
                out.unshift(list[i]);
            }
        }

        return out.join(' ');
    }

    /**
     * cache (or polyfill for <= IE8) Array.forEach()
     * source: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/forEach
     */
    var forEach = Array.prototype.forEach || function (fn, scope) {
            if (this === void 0 || this === null || typeof fn !== 'function') {
                throw new TypeError();
            }

            /* jshint bitwise: false */
            var i, len = this.length >>> 0;
            /* jshint bitwise: true */

            for (i = 0; i < len; ++i) {
                if (i in this) {
                    fn.call(scope, this[i], i, this);
                }
            }
        };

    // SVG Cache
    var svgCache = {};

    var injectCount = 0;
    var injectedElements = [];

    // Request Queue
    var requestQueue = [];

    // Script running status
    var ranScripts = {};

    var cloneSvg = function (sourceSvg) {
        return sourceSvg.cloneNode(true);
    };

    var queueRequest = function (url, callback) {
        requestQueue[url] = requestQueue[url] || [];
        requestQueue[url].push(callback);
    };

    var processRequestQueue = function (url) {
        for (var i = 0, len = requestQueue[url].length; i < len; i++) {
            // Make these calls async so we avoid blocking the page/renderer
            /* jshint loopfunc: true */
            (function (index) {
                setTimeout(function () {
                    requestQueue[url][index](cloneSvg(svgCache[url]));
                }, 0);
            })(i);
            /* jshint loopfunc: false */
        }
    };

    var loadSvg = function (url, callback) {
        if (svgCache[url] !== undefined) {
            if (svgCache[url] instanceof SVGSVGElement) {
                // We already have it in cache, so use it
                callback(cloneSvg(svgCache[url]));
            }
            else {
                // We don't have it in cache yet, but we are loading it, so queue this request
                queueRequest(url, callback);
            }
        }
        else {

            if (!window.XMLHttpRequest) {
                callback('Browser does not support XMLHttpRequest');
                return false;
            }

            // Seed the cache to indicate we are loading this URL already
            svgCache[url] = {};
            queueRequest(url, callback);

            var httpRequest = new XMLHttpRequest();

            httpRequest.onreadystatechange = function () {
                // readyState 4 = complete
                if (httpRequest.readyState === 4) {

                    // Handle status
                    if (httpRequest.status === 404 || httpRequest.responseXML === null) {
                        callback('Unable to load SVG file: ' + url);

                        if (isLocal) callback('Note: SVG injection ajax calls do not work locally without adjusting security setting in your browser. Or consider using a local webserver.');

                        callback();
                        return false;
                    }

                    // 200 success from server, or 0 when using file:// protocol locally
                    if (httpRequest.status === 200 || (isLocal && httpRequest.status === 0)) {

                        /* globals Document */
                        if (httpRequest.responseXML instanceof Document) {
                            // Cache it
                            svgCache[url] = httpRequest.responseXML.documentElement;
                        }
                        /* globals -Document */

                        // IE9 doesn't create a responseXML Document object from loaded SVG,
                        // and throws a "DOM Exception: HIERARCHY_REQUEST_ERR (3)" error when injected.
                        //
                        // So, we'll just create our own manually via the DOMParser using
                        // the the raw XML responseText.
                        //
                        // :NOTE: IE8 and older doesn't have DOMParser, but they can't do SVG either, so...
                        else if (DOMParser && (DOMParser instanceof Function)) {
                            var xmlDoc;
                            try {
                                var parser = new DOMParser();
                                xmlDoc = parser.parseFromString(httpRequest.responseText, 'text/xml');
                            }
                            catch (e) {
                                xmlDoc = undefined;
                            }

                            if (!xmlDoc || xmlDoc.getElementsByTagName('parsererror').length) {
                                callback('Unable to parse SVG file: ' + url);
                                return false;
                            }
                            else {
                                // Cache it
                                svgCache[url] = xmlDoc.documentElement;
                            }
                        }

                        // We've loaded a new asset, so process any requests waiting for it
                        processRequestQueue(url);
                    }
                    else {
                        callback('There was a problem injecting the SVG: ' + httpRequest.status + ' ' + httpRequest.statusText);
                        return false;
                    }
                }
            };

            httpRequest.open('GET', url);

            // Treat and parse the response as XML, even if the
            // server sends us a different mimetype
            if (httpRequest.overrideMimeType) httpRequest.overrideMimeType('text/xml');

            httpRequest.send();
        }
    };

    // Inject a single element
    var injectElement = function (el, evalScripts, pngFallback, callback) {

        // Grab the src or data-src attribute
        var imgUrl = el.getAttribute('data-src') || el.getAttribute('src');

        // We can only inject SVG
        if (!(/\.svg/i).test(imgUrl)) {
            callback('Attempted to inject a file with a non-svg extension: ' + imgUrl);
            return;
        }

        // If we don't have SVG support try to fall back to a png,
        // either defined per-element via data-fallback or data-png,
        // or globally via the pngFallback directory setting
        if (!hasSvgSupport) {
            var perElementFallback = el.getAttribute('data-fallback') || el.getAttribute('data-png');

            // Per-element specific PNG fallback defined, so use that
            if (perElementFallback) {
                el.setAttribute('src', perElementFallback);
                callback(null);
            }
            // Global PNG fallback directoriy defined, use the same-named PNG
            else if (pngFallback) {
                el.setAttribute('src', pngFallback + '/' + imgUrl.split('/').pop().replace('.svg', '.png'));
                callback(null);
            }
            // um...
            else {
                callback('This browser does not support SVG and no PNG fallback was defined.');
            }

            return;
        }

        // Make sure we aren't already in the process of injecting this element to
        // avoid a race condition if multiple injections for the same element are run.
        // :NOTE: Using indexOf() only _after_ we check for SVG support and bail,
        // so no need for IE8 indexOf() polyfill
        if (injectedElements.indexOf(el) !== -1) {
            return;
        }

        // Remember the request to inject this element, in case other injection
        // calls are also trying to replace this element before we finish
        injectedElements.push(el);

        // Try to avoid loading the orginal image src if possible.
        el.setAttribute('src', '');

        // Load it up
        loadSvg(imgUrl, function (svg) {

            if (typeof svg === 'undefined' || typeof svg === 'string') {
                callback(svg);
                return false;
            }

            var imgId = el.getAttribute('id');
            if (imgId) {
                svg.setAttribute('id', imgId);
            }

            var imgTitle = el.getAttribute('title');
            if (imgTitle) {
                svg.setAttribute('title', imgTitle);
            }

            // Concat the SVG classes + 'injected-svg' + the img classes
            var classMerge = [].concat(svg.getAttribute('class') || [], 'injected-svg', el.getAttribute('class') || []).join(' ');
            svg.setAttribute('class', uniqueClasses(classMerge));

            var imgStyle = el.getAttribute('style');
            if (imgStyle) {
                svg.setAttribute('style', imgStyle);
            }

            // Copy all the data elements to the svg
            var imgData = [].filter.call(el.attributes, function (at) {
                return (/^data-\w[\w\-]*$/).test(at.name);
            });
            forEach.call(imgData, function (dataAttr) {
                if (dataAttr.name && dataAttr.value) {
                    svg.setAttribute(dataAttr.name, dataAttr.value);
                }
            });

            // Make sure any internally referenced clipPath ids and their
            // clip-path references are unique.
            //
            // This addresses the issue of having multiple instances of the
            // same SVG on a page and only the first clipPath id is referenced.
            //
            // Browsers often shortcut the SVG Spec and don't use clipPaths
            // contained in parent elements that are hidden, so if you hide the first
            // SVG instance on the page, then all other instances lose their clipping.
            // Reference: https://bugzilla.mozilla.org/show_bug.cgi?id=376027

            // Handle all defs elements that have iri capable attributes as defined by w3c: http://www.w3.org/TR/SVG/linking.html#processingIRI
            // Mapping IRI addressable elements to the properties that can reference them:
            var iriElementsAndProperties = {
                'clipPath': ['clip-path'],
                'color-profile': ['color-profile'],
                'cursor': ['cursor'],
                'filter': ['filter'],
                'linearGradient': ['fill', 'stroke'],
                'marker': ['marker', 'marker-start', 'marker-mid', 'marker-end'],
                'mask': ['mask'],
                'pattern': ['fill', 'stroke'],
                'radialGradient': ['fill', 'stroke']
            };

            var element, elementDefs, properties, currentId, newId;
            Object.keys(iriElementsAndProperties).forEach(function (key) {
                element = key;
                properties = iriElementsAndProperties[key];

                elementDefs = svg.querySelectorAll('defs ' + element + '[id]');
                for (var i = 0, elementsLen = elementDefs.length; i < elementsLen; i++) {
                    currentId = elementDefs[i].id;
                    newId = currentId + '-' + injectCount;

                    // All of the properties that can reference this element type
                    var referencingElements;
                    forEach.call(properties, function (property) {
                        // :NOTE: using a substring match attr selector here to deal with IE "adding extra quotes in url() attrs"
                        referencingElements = svg.querySelectorAll('[' + property + '*="' + currentId + '"]');
                        for (var j = 0, referencingElementLen = referencingElements.length; j < referencingElementLen; j++) {
                            referencingElements[j].setAttribute(property, 'url(#' + newId + ')');
                        }
                    });

                    elementDefs[i].id = newId;
                }
            });

            // Remove any unwanted/invalid namespaces that might have been added by SVG editing tools
            svg.removeAttribute('xmlns:a');

            // Post page load injected SVGs don't automatically have their script
            // elements run, so we'll need to make that happen, if requested

            // Find then prune the scripts
            var scripts = svg.querySelectorAll('script');
            var scriptsToEval = [];
            var script, scriptType;

            for (var k = 0, scriptsLen = scripts.length; k < scriptsLen; k++) {
                scriptType = scripts[k].getAttribute('type');

                // Only process javascript types.
                // SVG defaults to 'application/ecmascript' for unset types
                if (!scriptType || scriptType === 'application/ecmascript' || scriptType === 'application/javascript') {

                    // innerText for IE, textContent for other browsers
                    script = scripts[k].innerText || scripts[k].textContent;

                    // Stash
                    scriptsToEval.push(script);

                    // Tidy up and remove the script element since we don't need it anymore
                    svg.removeChild(scripts[k]);
                }
            }

            // Run/Eval the scripts if needed
            if (scriptsToEval.length > 0 && (evalScripts === 'always' || (evalScripts === 'once' && !ranScripts[imgUrl]))) {
                for (var l = 0, scriptsToEvalLen = scriptsToEval.length; l < scriptsToEvalLen; l++) {

                    // :NOTE: Yup, this is a form of eval, but it is being used to eval code
                    // the caller has explictely asked to be loaded, and the code is in a caller
                    // defined SVG file... not raw user input.
                    //
                    // Also, the code is evaluated in a closure and not in the global scope.
                    // If you need to put something in global scope, use 'window'
                    new Function(scriptsToEval[l])(window); // jshint ignore:line
                }

                // Remember we already ran scripts for this svg
                ranScripts[imgUrl] = true;
            }

            // :WORKAROUND:
            // IE doesn't evaluate <style> tags in SVGs that are dynamically added to the page.
            // This trick will trigger IE to read and use any existing SVG <style> tags.
            //
            // Reference: https://github.com/iconic/SVGInjector/issues/23
            var styleTags = svg.querySelectorAll('style');
            forEach.call(styleTags, function (styleTag) {
                styleTag.textContent += '';
            });

            // Replace the image with the svg
            el.parentNode.replaceChild(svg, el);

            // Now that we no longer need it, drop references
            // to the original element so it can be GC'd
            delete injectedElements[injectedElements.indexOf(el)];
            el = null;

            // Increment the injected count
            injectCount++;

            callback(svg);
        });
    };

    /**
     * SVGInjector
     *
     * Replace the given elements with their full inline SVG DOM elements.
     *
     * :NOTE: We are using get/setAttribute with SVG because the SVG DOM spec differs from HTML DOM and
     * can return other unexpected object types when trying to directly access svg properties.
     * ex: "className" returns a SVGAnimatedString with the class value found in the "baseVal" property,
     * instead of simple string like with HTML Elements.
     *
     * @param {mixes} Array of or single DOM element
     * @param {object} options
     * @param {function} callback
     * @return {object} Instance of SVGInjector
     */
    var SVGInjector = function (elements, options, done) {

        // Options & defaults
        options = options || {};

        // Should we run the scripts blocks found in the SVG
        // 'always' - Run them every time
        // 'once' - Only run scripts once for each SVG
        // [false|'never'] - Ignore scripts
        var evalScripts = options.evalScripts || 'always';

        // Location of fallback pngs, if desired
        var pngFallback = options.pngFallback || false;

        // Callback to run during each SVG injection, returning the SVG injected
        var eachCallback = options.each;

        // Do the injection...
        if (elements.length !== undefined) {
            var elementsLoaded = 0;
            forEach.call(elements, function (element) {
                injectElement(element, evalScripts, pngFallback, function (svg) {
                    if (eachCallback && typeof eachCallback === 'function') eachCallback(svg);
                    if (done && elements.length === ++elementsLoaded) done(elementsLoaded);
                });
            });
        }
        else {
            if (elements) {
                injectElement(elements, evalScripts, pngFallback, function (svg) {
                    if (eachCallback && typeof eachCallback === 'function') eachCallback(svg);
                    if (done) done(1);
                    elements = null;
                });
            }
            else {
                if (done) done(0);
            }
        }
    };

    /* global module, exports: true, define */
    // Node.js or CommonJS
    if (typeof module === 'object' && typeof module.exports === 'object') {
        module.exports = exports = SVGInjector;
    }
    // AMD support
    else if (typeof define === 'function' && define.amd) {
        define(function () {
            return SVGInjector;
        });
    }
    // Otherwise, attach to window as global
    else if (typeof window === 'object') {
        window.SVGInjector = SVGInjector;
    }
    /* global -module, -exports, -define */

}(window, document));

// jQuery(document).ready(function($) {
//
//     /**
//      * inject .svg images
//      */
//     var mySVGsToInject = document.querySelectorAll('img.inject-me');
//     SVGInjector(mySVGsToInject);
//
//     $('.accordion-tabs-minimal').each(function(index) {
//         $(this).children('li').first().children('a').addClass('is-active').next().addClass('is-open').show();
//     });
//     $('.accordion-tabs-minimal').on('click', 'li > a.tab-link', function(event) {
//         if (!$(this).hasClass('is-active')) {
//             event.preventDefault();
//             var accordionTabs = $(this).closest('.accordion-tabs-minimal');
//             accordionTabs.find('.is-open').removeClass('is-open').hide();
//
//             $(this).next().toggleClass('is-open').toggle();
//             accordionTabs.find('.is-active').removeClass('is-active');
//             $(this).addClass('is-active');
//         } else {
//             event.preventDefault();
//         }
//     });
// });
//


// Get IE or Edge browser version
var version = detectIE();

/**
 * detect IE
 * returns version of IE or false, if browser is not Internet Explorer
 * https://codepen.io/gapcode/pen/vEJNZN
 */
function detectIE() {
    var ua = window.navigator.userAgent;

    // Test values; Uncomment to check result …

    // IE 10
    // ua = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/6.0)';

    // IE 11
    // ua = 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko';

    // Edge 12 (Spartan)
    // ua = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36 Edge/12.0';

    // Edge 13
    // ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Safari/537.36 Edge/13.10586';

    var msie = ua.indexOf('MSIE ');
    if (msie > 0) {
        // IE 10 or older => return version number
        return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
    }

    var trident = ua.indexOf('Trident/');
    if (trident > 0) {
        // IE 11 => return version number
        var rv = ua.indexOf('rv:');
        return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
    }

    var edge = ua.indexOf('Edge/');
    if (edge > 0) {
        // Edge (IE 12+) => return version number
        return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
    }

    // other browser
    return false;
}


/**
 * add Class
 * add class to given element
 *
 * usage examples:
 * addClass('.class-selector', 'example-class');
 * addClass('#id-selector', 'example-class');
 *
 */
function addClass(selector, myClass) {

    // get all elements that match our selector
    elements = document.querySelectorAll(selector);

    // add class to all chosen elements
    for (var i=0; i<elements.length; i++) {
        elements[i].classList.add(myClass);
    }
}

/**
 * add Class
 * add class to given element
 *
 * usage examples:
 * removeClass('.class-selector', 'example-class');
 * removeClass('#id-selector', 'example-class');
 *
 */
function removeClass(selector, myClass) {

    // get all elements that match our selector
    elements = document.querySelectorAll(selector);

    // remove class from all chosen elements
    for (var i=0; i<elements.length; i++) {
        elements[i].classList.remove(myClass);
    }
}

var isWebkit = 'WebkitAppearance' in document.documentElement.style;

if(isWebkit && version === false) {
    addClass('.html', 'webkit');
}
