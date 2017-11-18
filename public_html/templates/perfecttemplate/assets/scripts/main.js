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
