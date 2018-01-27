'use strict';

// Generates a custom Modernizr build
module.exports = {
    dist: {
        "cache": true,
        "dest": "<%= paths.template %>/js/modernizr.js",
        "options": [
            "html5shiv",
            "prefixedCSS",
            "setClasses"
        ],
        "uglify": false,
        "tests": [
            "eventlistener",
            "appearance",
            "boxshadow",
            "checked",
            "cssanimations",
            "csscalc", // For the grid
            "flexbox",
            "flexboxlegacy",
            "flexboxtweener",
            "flexwrap",
            "localstorage",
            "svg",
            "touchevents"
        ],
        "crawl" : false,
        "customTests" : []
    }
};
