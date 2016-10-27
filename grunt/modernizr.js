module.exports = {
    // configure modernizr -----------------------------------------------------
    dist: {
        "cache": true,
        "dest": "<%= config.dest %>/js/modernizr.js",
        "options": [
            "html5shiv",
            "prefixedCSS",
            "setClasses"
            ],
        "uglify": false,
        "tests": [
            "appearance",
            "csstransforms",
            "cssfilters",
            "cssgradients",
            "csscolumns",
            "checked",
            "flexbox",
            "flexboxlegacy",
            "flexboxtweener",
            "flexwrap",
            "svg",
            "localstorage"
            ],
        "crawl" : false,
        "customTests" : []
    }
};
