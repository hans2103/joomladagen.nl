/*
 joomladagen 2016-12-14 
*/
!function(a,b){"function"==typeof define&&define.amd?define(b):"object"==typeof exports?module.exports=b:a.apollo=b()}(this,function(){"use strict";var a,b,c,d,e={},f=function(a,b){"[object Array]"!==Object.prototype.toString.call(a)&&(a=a.split(" "));for(var c=0;c<a.length;c++)b(a[c],c)};return"classList"in document.documentElement?(a=function(a,b){return a.classList.contains(b)},b=function(a,b){a.classList.add(b)},c=function(a,b){a.classList.remove(b)},d=function(a,b){a.classList.toggle(b)}):(a=function(a,b){return new RegExp("(^|\\s)"+b+"(\\s|$)").test(a.className)},b=function(b,c){a(b,c)||(b.className+=(b.className?" ":"")+c)},c=function(b,c){a(b,c)&&(b.className=b.className.replace(new RegExp("(^|\\s)*"+c+"(\\s|$)*","g"),""))},d=function(d,e){(a(d,e)?c:b)(d,e)}),e.hasClass=function(b,c){return a(b,c)},e.addClass=function(a,c){f(c,function(c){b(a,c)})},e.removeClass=function(a,b){f(b,function(b){c(a,b)})},e.toggleClass=function(a,b){f(b,function(b){d(a,b)})},e}),function(a,b){"function"==typeof define&&define.amd?define("responsivemenu",b(a)):"object"==typeof exports?module.responsivemenu=b(a):a.responsivemenu=b(a)}(this,function(a){"use strict";function b(a,b,c){for(var d=[];a.parentNode&&a.parentNode!=c;)a=a.parentNode,a.tagName==b&&d.push(a);return d}function c(a,b){var c=document.getElementById(a);if(window.getComputedStyle)var d=document.defaultView.getComputedStyle(c,null).getPropertyValue(b);else if(c.currentStyle)var d=c.currentStyle[b];return d}function d(a){function d(){k=c(a.mobileindicatorid,"z-index"),0!=k||apollo.hasClass(f,a.openclass)?1==k&&(apollo.addClass(o,a.hideclass),apollo.removeClass(o,a.toggleclosedclass),i&&l(j,function(b,c){apollo.removeClass(j[c].parentNode.getElementsByTagName("ul")[0],a.hideclass),apollo.addClass(j[c],a.hideclass)}),apollo.removeClass(f,[a.openclass,a.hideclass]),apollo.addClass(f,a.fullmenuclass),apollo.removeClass(document.body,a.openbodyclass),1==a.absolute&&apollo.hasClass(f,a.absolutemenuclass)&&apollo.removeClass(f,a.absolutemenuclass)):(apollo.removeClass(o,a.hideclass),i&&l(j,function(b,c){apollo.addClass(j[c].parentNode.getElementsByTagName("ul")[0],a.hideclass),apollo.removeClass(j[c],a.hideclass)}),apollo.removeClass(f,[a.openclass,a.fullmenuclass]),apollo.addClass(f,a.hideclass),apollo.removeClass(document.body,a.openbodyclass),1==a.absolute&&apollo.addClass(f,a.absolutemenuclass))}function e(){if(1==a.sticky){var b=a.wrapper.offsetHeight,c=Math.max(document.documentElement.clientHeight,window.innerHeight||0);if(c<=b&&!apollo.hasClass(document.body,a.bodyoverflowhiddenclass))apollo.addClass(document.body,a.bodyoverflowhiddenclass),apollo.addClass(a.wrapper,a.menuoverflowautoclass);else if(c>b&&(apollo.hasClass(document.body,a.bodyoverflowhiddenclass)&&(apollo.removeClass(document.body,a.bodyoverflowhiddenclass),apollo.removeClass(a.wrapper,a.menuoverflowautoclass)),apollo.hasClass(a.wrapper,a.stickyclass)||apollo.addClass(a.wrapper,a.stickyclass),!apollo.hasClass(f,a.openclass)&&!apollo.hasClass(document.body,a.stickyinitiatedclass))){var d=b.toString()+"px";document.body.setAttribute("style","padding-top:"+d),apollo.addClass(document.body,a.stickyinitiatedclass)}}}f=""==a.menu?a.wrapper.getElementsByTagName("ul")[0]:a.menu,apollo.addClass(a.wrapper,a.initiated_class),a.onAfterInit();var g=f.querySelectorAll("li ul");g.length&&(i=!0,j=document.getElementsByClassName(a.subtoggleclass));var h=document.createElement("div");document.body.appendChild(h),h.id=a.mobileindicatorid;var k=0,m=document.createElement(a.toggletype);apollo.addClass(m,[a.toggleclass,a.hideclass]),""==a.before_element&&(a.before_element=a.wrapper.firstChild),a.before_element.parentNode.insertBefore(m,a.before_element);var o=document.getElementsByClassName(a.toggleclass)[0];if(o.innerHTML=a.togglecontent,o.setAttribute("aria-hidden","true"),o.setAttribute("aria-pressed","false"),o.setAttribute("type","button"),i)for(var p=0;p<g.length;p++){var q=document.createElement(a.subtoggletype);apollo.addClass(q,[a.subtoggleclass,a.hideclass]);var r=g[p].parentNode;r.insertBefore(q,r.firstChild),q.innerHTML=a.subtogglecontent,q.setAttribute("aria-hidden","true"),q.setAttribute("aria-pressed","false"),q.setAttribute("type","button"),apollo.addClass(g[p].parentNode,a.parentclass)}window.addEventListener("load",function(){d(),e()},!0),window.addEventListener("resize",function(){d(),e(),n(function(){d(),e()},200)},!0);for(var s=f.getElementsByTagName("a"),p=0;p<s.length;p++)s[p].onblur=function(){for(var b=document.getElementsByClassName("rm-focused"),c=0;c<b.length;c++)apollo.removeClass(b[c],a.focusedclass)},s[p].onfocus=function(){var c=this.parentNode.parentNode.querySelectorAll("li");if(c.length)for(var d=0;d<c.length;d++)apollo.removeClass(c[d],a.focusedclass);var e=b(this,"LI",f);if(e.length)for(var d=0;d<e.length;d++)apollo.addClass(e[d],a.focusedclass)};o.onclick=function(){return apollo.hasClass(f,a.hideclass)?(a.onBeforeToggleOpen(),apollo.removeClass(f,a.hideclass),apollo.addClass(f,a.openclass),apollo.addClass(document.body,a.openbodyclass),apollo.addClass(o,a.toggleclosedclass),apollo.addClass(f,a.animateopenclass),setTimeout(function(){apollo.removeClass(f,a.animateopenclass),a.onAfterToggleOpen()},a.animateduration)):apollo.hasClass(f,a.openclass)&&(a.onBeforeToggleClose(),apollo.addClass(f,a.animatecloseclass),apollo.removeClass(o,a.toggleclosedclass),setTimeout(function(){apollo.removeClass(f,a.animatecloseclass),apollo.removeClass(f,a.openclass),apollo.addClass(f,a.hideclass),apollo.removeClass(document.body,a.openbodyclass),a.onAfterToggleClose()},a.animateduration)),e(),!1},i&&l(j,function(b,c){var d=j[c],g=d.parentNode.getElementsByTagName("ul")[0];d.onclick=function(){apollo.hasClass(g,a.hideclass)?(a.onBeforeSubToggleOpen(),apollo.addClass(f,a.subanimateopenclass),apollo.addClass(d,a.toggleclosedclass),apollo.removeClass(g,a.hideclass),setTimeout(function(){apollo.removeClass(f,a.subanimateopenclass),a.onAfterSubToggleOpen()},a.subanimateduration)):apollo.hasClass(g,a.hideclass)||(a.onBeforeSubToggleClose(),apollo.addClass(f,a.subanimatecloseclass),apollo.removeClass(d,a.toggleclosedclass),setTimeout(function(){apollo.removeClass(f,a.subanimatecloseclass),apollo.addClass(g,a.hideclass),a.onAfterSubToggleClose()},a.subanimateduration)),e()}})}var e,f,g={},h=!!document.querySelector&&!!a.addEventListener,i=!1,j=!1,k={menu:"",initiated_class:"rm-initiated",before_element:"",toggletype:"button",toggleclass:"rm-togglebutton",toggleclosedclass:"rm-togglebutton--closed",togglecontent:"menu",subtoggletype:"button",subtoggleclass:"rm-subtoggle",subtogglecontent:"+",sticky:0,absolute:0,hideclass:"rm-closed",openclass:"rm-opened",openbodyclass:"has-opened-menu",focusedclass:"rm-focused",animateopenclass:"is-opening",animatecloseclass:"is-closing",animateduration:0,subanimateopenclass:"is-opening",subanimatecloseclass:"is-closing",subanimateduration:0,parentclass:"rm-parent",fullmenuclass:"rm-fullmenu",absolutemenuclass:"rm-absolutemenu",bodyoverflowhiddenclass:"rm-bodyoverflowhidden",menuoverflowautoclass:"rm-menuoverflowauto",stickyclass:"rm-sticky",stickyinitiatedclass:"rm-sticky-initiated",noresponsivemenuclass:"rm-no-responsive-menu",mobileindicatorid:"rm-mobile-indicator",onAfterInit:function(){},onBeforeToggleOpen:function(){},onAfterToggleOpen:function(){},onBeforeToggleClose:function(){},onAfterToggleClose:function(){},onBeforeSubToggleOpen:function(){},onAfterSubToggleOpen:function(){},onBeforeSubToggleClose:function(){},onAfterSubToggleClose:function(){}},l=function(a,b,c){if("[object Object]"===Object.prototype.toString.call(a))for(var d in a)Object.prototype.hasOwnProperty.call(a,d)&&b.call(c,a[d],d,a);else for(var e=0,f=a.length;e<f;e++)b.call(c,a[e],e,a)},m=function(a,b){var c={};return l(a,function(b,d){c[d]=a[d]}),l(b,function(a,d){c[d]=b[d]}),c},n=function(){var a={};return function(b,c,d){d||(d="Don't call this twice without a uniqueId"),a[d]&&clearTimeout(a[d]),a[d]=setTimeout(b,c)}}();return g.init=function(a){return h?(e=m(k,a||{}),void d(e)):void(document.documentElement.className+=" "+e.noresponsivemenuclass)},g}),function(a,b){"use strict";function c(a){a=a.split(" ");for(var b={},c=a.length,d=[];c--;)b.hasOwnProperty(a[c])||(b[a[c]]=1,d.unshift(a[c]));return d.join(" ")}var d="file:"===a.location.protocol,e=b.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#BasicStructure","1.1"),f=Array.prototype.forEach||function(a,b){if(void 0===this||null===this||"function"!=typeof a)throw new TypeError;var c,d=this.length>>>0;for(c=0;c<d;++c)c in this&&a.call(b,this[c],c,this)},g={},h=0,i=[],j=[],k={},l=function(a){return a.cloneNode(!0)},m=function(a,b){j[a]=j[a]||[],j[a].push(b)},n=function(a){for(var b=0,c=j[a].length;b<c;b++)!function(b){setTimeout(function(){j[a][b](l(g[a]))},0)}(b)},o=function(b,c){if(void 0!==g[b])g[b]instanceof SVGSVGElement?c(l(g[b])):m(b,c);else{if(!a.XMLHttpRequest)return c("Browser does not support XMLHttpRequest"),!1;g[b]={},m(b,c);var e=new XMLHttpRequest;e.onreadystatechange=function(){if(4===e.readyState){if(404===e.status||null===e.responseXML)return c("Unable to load SVG file: "+b),d&&c("Note: SVG injection ajax calls do not work locally without adjusting security setting in your browser. Or consider using a local webserver."),c(),!1;if(!(200===e.status||d&&0===e.status))return c("There was a problem injecting the SVG: "+e.status+" "+e.statusText),!1;if(e.responseXML instanceof Document)g[b]=e.responseXML.documentElement;else if(DOMParser&&DOMParser instanceof Function){var a;try{var f=new DOMParser;a=f.parseFromString(e.responseText,"text/xml")}catch(b){a=void 0}if(!a||a.getElementsByTagName("parsererror").length)return c("Unable to parse SVG file: "+b),!1;g[b]=a.documentElement}n(b)}},e.open("GET",b),e.overrideMimeType&&e.overrideMimeType("text/xml"),e.send()}},p=function(b,d,g,j){var l=b.getAttribute("data-src")||b.getAttribute("src");if(!/\.svg/i.test(l))return void j("Attempted to inject a file with a non-svg extension: "+l);if(!e){var m=b.getAttribute("data-fallback")||b.getAttribute("data-png");return void(m?(b.setAttribute("src",m),j(null)):g?(b.setAttribute("src",g+"/"+l.split("/").pop().replace(".svg",".png")),j(null)):j("This browser does not support SVG and no PNG fallback was defined."))}i.indexOf(b)===-1&&(i.push(b),b.setAttribute("src",""),o(l,function(e){if("undefined"==typeof e||"string"==typeof e)return j(e),!1;var g=b.getAttribute("id");g&&e.setAttribute("id",g);var m=b.getAttribute("title");m&&e.setAttribute("title",m);var n=[].concat(e.getAttribute("class")||[],"injected-svg",b.getAttribute("class")||[]).join(" ");e.setAttribute("class",c(n));var o=b.getAttribute("style");o&&e.setAttribute("style",o);var p=[].filter.call(b.attributes,function(a){return/^data-\w[\w\-]*$/.test(a.name)});f.call(p,function(a){a.name&&a.value&&e.setAttribute(a.name,a.value)});var q,r,s,t,u,v={clipPath:["clip-path"],"color-profile":["color-profile"],cursor:["cursor"],filter:["filter"],linearGradient:["fill","stroke"],marker:["marker","marker-start","marker-mid","marker-end"],mask:["mask"],pattern:["fill","stroke"],radialGradient:["fill","stroke"]};Object.keys(v).forEach(function(a){q=a,s=v[a],r=e.querySelectorAll("defs "+q+"[id]");for(var b=0,c=r.length;b<c;b++){t=r[b].id,u=t+"-"+h;var d;f.call(s,function(a){d=e.querySelectorAll("["+a+'*="'+t+'"]');for(var b=0,c=d.length;b<c;b++)d[b].setAttribute(a,"url(#"+u+")")}),r[b].id=u}}),e.removeAttribute("xmlns:a");for(var w,x,y=e.querySelectorAll("script"),z=[],A=0,B=y.length;A<B;A++)x=y[A].getAttribute("type"),x&&"application/ecmascript"!==x&&"application/javascript"!==x||(w=y[A].innerText||y[A].textContent,z.push(w),e.removeChild(y[A]));if(z.length>0&&("always"===d||"once"===d&&!k[l])){for(var C=0,D=z.length;C<D;C++)new Function(z[C])(a);k[l]=!0}var E=e.querySelectorAll("style");f.call(E,function(a){a.textContent+=""}),b.parentNode.replaceChild(e,b),delete i[i.indexOf(b)],b=null,h++,j(e)}))},q=function(a,b,c){b=b||{};var d=b.evalScripts||"always",e=b.pngFallback||!1,g=b.each;if(void 0!==a.length){var h=0;f.call(a,function(b){p(b,d,e,function(b){g&&"function"==typeof g&&g(b),c&&a.length===++h&&c(h)})})}else a?p(a,d,e,function(b){g&&"function"==typeof g&&g(b),c&&c(1),a=null}):c&&c(0)};"object"==typeof module&&"object"==typeof module.exports?module.exports=exports=q:"function"==typeof define&&define.amd?define(function(){return q}):"object"==typeof a&&(a.SVGInjector=q)}(window,document),jQuery(document).ready(function(a){var b=document.querySelectorAll("img.inject-me");SVGInjector(b),a(".accordion-tabs-minimal").each(function(b){a(this).children("li").first().children("a").addClass("is-active").next().addClass("is-open").show()}),a(".accordion-tabs-minimal").on("click","li > a.tab-link",function(b){if(a(this).hasClass("is-active"))b.preventDefault();else{b.preventDefault();var c=a(this).closest(".accordion-tabs-minimal");c.find(".is-open").removeClass("is-open").hide(),a(this).next().toggleClass("is-open").toggle(),c.find(".is-active").removeClass("is-active"),a(this).addClass("is-active")}})});