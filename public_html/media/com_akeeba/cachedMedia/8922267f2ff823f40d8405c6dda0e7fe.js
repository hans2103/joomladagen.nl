// ============================================================================
// /media/com_akeeba/js/UserInterfaceCommon.min.js
// ============================================================================

/*
 Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 @license   GNU General Public License version 3, or later
 Math.uuid.js (v1.4)
 http://www.broofa.com
 mailto:robert@broofa.com

 Copyright (c) 2009 Robert Kieffer
 Dual licensed under the MIT and GPL licenses.

 Usage: Math.uuid()
*/
if("undefined"==typeof akeeba)var akeeba={};Math.uuid=function(){var a="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz".split("");return function(b,c){var e=[];c=c||a.length;if(b)for(var d=0;d<b;d++)e[d]=a[0|Math.random()*c];else for(e[8]=e[13]=e[18]=e[23]="-",e[14]="4",d=0;36>d;d++)e[d]||(b=0|16*Math.random(),e[d]=a[19==d?b&3|8:b]);return e.join("")}}();
function basename(a,b){a=a.replace(/^.*[\/\\]/g,"");"string"==typeof b&&a.substr(a.length-b.length)==b&&(a=a.substr(0,a.length-b.length));return a}function empty(a){var b;if(""===a||0===a||"0"===a||null===a||!1===a||"undefined"===typeof a)return!0;if("object"==typeof a){for(b in a)return!1;return!0}return!1}function array_shift(a){if(0===a.length)return null;if(0<a.length)return a.shift()}
function trim(a,b){var c;a+="";b=b?(b+"").replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^:])/g,"$1"):" \n\r\t\f\x0B\u00a0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";var e=a.length;for(c=0;c<e;c++)if(-1===b.indexOf(a.charAt(c))){a=a.substring(c);break}e=a.length;for(c=e-1;0<=c;c--)if(-1===b.indexOf(a.charAt(c))){a=a.substring(0,c+1);break}return-1===b.indexOf(a.charAt(0))?a:""}
Object.keys||(Object.keys=function(){var a=Object.prototype.hasOwnProperty,b=!{toString:null}.propertyIsEnumerable("toString"),c="toString toLocaleString valueOf hasOwnProperty isPrototypeOf propertyIsEnumerable constructor".split(" "),e=c.length;return function(d){if("object"!==typeof d&&("function"!==typeof d||null===d))throw new TypeError("Object.keys called on non-object");var g=[],f;for(f in d)a.call(d,f)&&g.push(f);if(b)for(f=0;f<e;f++)a.call(d,c[f])&&g.push(c[f]);return g}}());
function escapeHTML(a){return a.split("&").join("&amp;").split("<").join("&lt;").split(">").join("&gt;")}; //# sourceMappingURL=UserInterfaceCommon.min.map

// ============================================================================
// /media/com_akeeba/js/Modal.min.js
// ============================================================================

/*
 Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 @license   GNU General Public License version 3, or later
 jsModal - A pure JavaScript modal dialog engine v1.0d
 http://jsmodal.com/

 Author: Henry Rune Tang Kai <henry@henrys.se>

 (c) Copyright 2013 Henry Tang Kai.

 License: http://www.opensource.org/licenses/mit-license.php

 Date: 2013-7-11

 Modified by Akeeba Ltd:
 - [Oct 2016] Prefix "akeeba-modal-" instead of generic "modal-" to avoid conflicts with 3PD software e.g. Bootstrap.
 - [Oct 2016] Remove support for AJAX content. We use our own AJAX handlers which work around 3PD plugins corrupting content.
 ? [Oct 2016] Added method show() which just calls open() for backwards compatibility reasons.
 ? [Oct 2016] Added parameter "inherit" where you can give a query selector for an element whose content will be inherited by the modal.
 ? [Oct 2016] open() returns the Modal object so that we can interact with it through external code.
 ? [Nov 2016] Added parameter "iframe" where you can give a URL to load inside an IFRAME.
*/
if("undefined"==typeof akeeba)var akeeba={};
"undefined"==typeof akeeba.Modal&&(akeeba.Modal=function(){var d={},b={},e=document.createElement("div"),c=document.createElement("div"),h=document.createElement("div"),f=document.createElement("div"),l=document.createElement("div"),k=null,m,n;d.show=function(a){try{console.log("Using akeeba.Modal.show() is deprecated. Use .open() instead.")}catch(g){}return d.open(a)};d.open=function(a){b.width=a.width||"auto";b.height=a.height||"auto";b.lock=a.lock||!1;b.hideClose=a.hideClose||!1;b.draggable=a.draggable||
!1;b.closeAfter=a.closeAfter||0;b.closeCallback=a.closeCallback||!1;b.openCallback=a.openCallback||!1;b.hideOverlay=a.hideOverlay||!1;m=function(){d.center({})};f.innerHTML="";var g=null;if(a.content)f.innerHTML=a.content;else if(a.inherit){if(g=a.inherit,"string"==typeof a.inherit&&(g=window.document.querySelector(a.inherit)),null!=g&&g.innerHTML)for(k=g;0<k.childNodes.length;)f.appendChild(k.childNodes[0])}else a.iframe&&(g=window.document.createElement("iframe"),g.setAttribute("src",a.iframe),
g.setAttribute("width",a.width),g.setAttribute("height",a.height),g.setAttribute("frameborder",0),g.setAttribute("marginheight",0),g.setAttribute("marginwidth",0),f.appendChild(g));c.style.width=b.width;c.style.height=b.height;d.center({});if(b.lock||b.hideClose)l.style.visibility="hidden";b.hideOverlay||(e.style.visibility="visible");c.style.visibility="visible";document.onkeypress=function(a){27===a.keyCode&&!0!==b.lock&&d.close()};l.onclick=function(){if(b.hideClose)return!1;d.close()};e.onclick=
function(){if(b.lock)return!1;d.close()};window.addEventListener?window.addEventListener("resize",m,!1):window.attachEvent&&window.attachEvent("onresize",m);b.draggable?(h.style.cursor="move",h.onmousedown=function(a){d.drag(a);return!1}):h.onmousedown=function(){return!1};0<b.closeAfter&&(n=window.setTimeout(function(){d.close()},1E3*b.closeAfter));b.openCallback&&b.openCallback();return this};d.drag=function(a){var b=void 0!==window.event?window.event.clientX:a.clientX,d=void 0!==window.event?window.event.clientY:
a.clientY,e=b-c.offsetLeft,f=d-c.offsetTop;document.onmousemove=function(a){b=void 0!==window.event?window.event.clientX:a.clientX;d=void 0!==window.event?window.event.clientY:a.clientY;c.style.left=0<b-e?b-e+"px":0;c.style.top=0<d-f?d-f+"px":0;document.onmouseup=function(){window.document.onmousemove=null}}};d.close=function(){if(!function(a){var b;if(""===a||0===a||"0"===a||null===a||!1===a||"undefined"===typeof a)return!0;if("object"==typeof a){for(b in a)return!1;return!0}return!1}(k)){for(;0<
f.childNodes.length;)k.appendChild(f.childNodes[0]);k=null}f.innerHTML="";e.setAttribute("style","");e.style.cssText="";e.style.visibility="hidden";c.setAttribute("style","");c.style.cssText="";c.style.visibility="hidden";h.style.cursor="default";l.setAttribute("style","");l.style.cssText="";n&&window.clearTimeout(n);b.closeCallback&&b.closeCallback();window.removeEventListener?window.removeEventListener("resize",m,!1):window.detachEvent&&window.detachEvent("onresize",m)};d.center=function(a){var b=
Math.max(document.body.scrollHeight,document.documentElement.scrollHeight),d=Math.max(c.clientWidth,c.offsetWidth),f=Math.max(c.clientHeight,c.offsetHeight),h=0,l=0,k=0,m=0;"number"===typeof window.innerWidth?(h=window.innerWidth,l=window.innerHeight):document.documentElement&&document.documentElement.clientWidth&&(h=document.documentElement.clientWidth,l=document.documentElement.clientHeight);"number"===typeof window.pageYOffset?(m=window.pageYOffset,k=window.pageXOffset):document.body&&document.body.scrollLeft?
(m=document.body.scrollTop,k=document.body.scrollLeft):document.documentElement&&document.documentElement.scrollLeft&&(m=document.documentElement.scrollTop,k=document.documentElement.scrollLeft);a.horizontalOnly||(c.style.top=m+l/2-f/2+"px");c.style.left=k+h/2-d/2+"px";e.style.height=b+"px";e.style.width="100%"};e.setAttribute("id","akeeba-modal-overlay");c.setAttribute("id","akeeba-modal-container");h.setAttribute("id","akeeba-modal-header");f.setAttribute("id","akeeba-modal-content");l.setAttribute("id",
"akeeba-modal-close");h.appendChild(l);c.appendChild(h);c.appendChild(f);e.style.visibility="hidden";c.style.visibility="hidden";window.addEventListener?window.addEventListener("load",function(){document.body.appendChild(e);document.body.appendChild(c)},!1):window.attachEvent&&window.attachEvent("onload",function(){document.body.appendChild(e);document.body.appendChild(c)});return d}()); //# sourceMappingURL=Modal.min.map

// ============================================================================
// /media/com_akeeba/js/Ajax.min.js
// ============================================================================

/*
 Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 @license   GNU General Public License version 3, or later
*/
if("undefined"==typeof akeeba)var akeeba={};"undefined"==typeof akeeba.Ajax&&(akeeba.Ajax={xhrSuccessStatus:{0:200,1223:204},requestArray:[],processingQueue:!1});
akeeba.Ajax.ajax=function(b,a){"undefined"==typeof a&&(a=b,b=a.url);var c="undefined"==typeof a.type?"POST":a.type;c=c.toUpperCase();var e="undefined"==typeof a.data?{}:a.data,g=null,h="undefined"==typeof a.success?null:a.success,f="undefined"==typeof a.error?null:a.error;if("undefined"==typeof a.cache||!a.url){var k=(new Date).getTime()/1E3,l=parseInt(k,10);e._cacheBustingJunk=Math.round(1E3*(k-l))/1E3}"POST"==c||"PUT"==c?g=this.interpolateParameters(e):(b+=-1==b.indexOf("?")?"?":"&",b+=this.interpolateParameters(e));
var d=new XMLHttpRequest;d.open(c,b);"POST"!=c&&"PUT"!=c||d.setRequestHeader("Content-Type","application/x-www-form-urlencoded");d.onload=function(a){a=akeeba.Ajax.xhrSuccessStatus[d.status]||d.status;var b=d.statusText,c="text"!==(d.responseType||"text")||"string"!==typeof d.responseText?d.response:d.responseText;d.getAllResponseHeaders();200===a?null!=h&&akeeba.Ajax.triggerCallbacks(h,c,b,d):f&&akeeba.Ajax.triggerCallbacks(f,d,"error",null)};d.onerror=function(a){f&&akeeba.Ajax.triggerCallbacks(f,
d,"error",null)};window.attachEvent&&!window.addEventListener&&(d.onreadystatechange=function(){if(4===this.readyState){var a=akeeba.Ajax.xhrSuccessStatus[this.status]||this.status;if(200<=a&&400>a)d.onload();else d.onerror()}});d.ontimeout=function(){f&&akeeba.Ajax.triggerCallbacks(f,d,"timeout",null)};d.onabort=function(){f&&akeeba.Ajax.triggerCallbacks(f,d,"abort",null)};b="undefined"==typeof a.timeout?6E5:a.timeout;0<b&&(d.timeout=b);"undefined"!=typeof a.beforeSend&&!1===a.beforeSend(d,a)||d.send(g)};
akeeba.Ajax.enqueue=function(b,a){"undefined"===typeof a&&(a=b,b=a.url);a.url=b;akeeba.Ajax.requestArray.push(a);akeeba.Ajax.processQueue()};akeeba.Ajax.interpolateParameters=function(b,a){a=a||"";var c="",e;for(e in b)b.hasOwnProperty(e)&&(0<c.length&&(c+="&"),c="object"!==typeof b[e]?""===a?c+(encodeURIComponent(e)+"="+encodeURIComponent(b[e])):c+(encodeURIComponent(a)+"["+encodeURIComponent(e)+"]="+encodeURIComponent(b[e])):c+akeeba.Ajax.interpolateParameters(b[e],e));return c};
akeeba.Ajax.triggerCallbacks=function(){var b=Array.prototype.slice.call(arguments),a=b.shift();if("function"==typeof a)return a.apply(null,b);if(a instanceof Array)for(var c=0;c<a.length;c++)if(!1===a[c].apply(null,b))return!1;return null};akeeba.Ajax.processQueueHelper=function(){akeeba.Ajax.processingQueue=!1;setTimeout(akeeba.Ajax.processQueue,50)};
akeeba.Ajax.processQueue=function(){if(!akeeba.Ajax.requestArray.length)akeeba.Ajax.processingQueue=!1;else if(!akeeba.Ajax.processingQueue){var b=akeeba.Ajax.requestArray.shift(),a=b.url,c="undefined"==typeof b.success?[]:b.success,e="undefined"==typeof b.error?[]:b.error;"object"==typeof c&&c instanceof Array||(c=[c]);"object"==typeof e&&e instanceof Array||(e=[e]);c.unshift(akeeba.Ajax.processQueueHelper);e.unshift(akeeba.Ajax.processQueueHelper);b.success=c;b.error=e;akeeba.Ajax.processingQueue=
!0;akeeba.Ajax.ajax(a,b)}}; //# sourceMappingURL=Ajax.min.map

// ============================================================================
// /media/com_akeeba/js/System.min.js
// ============================================================================

/*
 Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 @license   GNU General Public License version 3, or later
*/
if("undefined"===typeof akeeba)var akeeba={};"undefined"===typeof akeeba.System&&(akeeba.System={},akeeba.System.documentReady=function(a,b){},akeeba.System.notification={hasDesktopNotification:!1,iconURL:""},akeeba.System.params={AjaxURL:"",errorCallback:akeeba.System.modalErrorHandler,password:"",errorDialogId:"errorDialog",errorDialogMessageId:"errorDialogPre"},akeeba.System.modalDialog=null,akeeba.System.optionsStorage=null);
akeeba.System.array_merge=function(){var a=Array.prototype.slice.call(arguments),b={},c,d;var e=!0;for(d=0;d<a.length;d++)if(!(a[d]instanceof Array)){e=!1;break}if(e){e=[];for(d=0;d<a.length;d++)e=e.concat(a[d]);return e}var f;for(f=d=0;d<a.length;d++)if(a[d]instanceof Array)for(e=0;e<a[d].length;e++)b[f++]=a[d][e];else for(c in a[d])a[d].hasOwnProperty(c)&&(parseInt(c,10)+""===c?b[f++]=a[d][c]:b[c]=a[d][c]);return b};
akeeba.System.array_diff=function(a){var b={},c=arguments.length,d="",e,f="";a:for(d in a)for(e=1;e<c;e++){var g=arguments[e];for(f in g)if(g[f]===a[d])continue a;b[d]=a[d]}return b};
akeeba.System.getOptions=function(a,b){"undefined"==typeof b&&(b=null);akeeba.System.optionsStorage||(akeeba.System.loadJoomlaOptions(),akeeba.System.loadOptions());return void 0!==akeeba.System.optionsStorage[a]?akeeba.System.optionsStorage[a]:"undefined"!==typeof Joomla&&"undefined"!==typeof Joomla.getOptions?Joomla.getOptions(a,b):b};
akeeba.System.loadJoomlaOptions=function(){var a=document.querySelectorAll(".joomla-script-options");akeeba.System.iterateNodes(a,function(a){if(!akeeba.System.hasClass(a,"akeebaImported")){var b;str=a.text||a.textContent;(b=JSON.parse(str))&&akeeba.System.loadOptions(b);akeeba.System.addClass(a,"akeebaImported")}})};
akeeba.System.loadOptions=function(a){if(!a){for(var b=document.querySelectorAll(".akeeba-script-options.new"),c,d,e=0,f=0,g=b.length;f<g;f++){d=b[f];c=d.text||d.textContent;if(c=JSON.parse(c))akeeba.System.loadOptions(c),e++;akeeba.System.removeClass(d,"new");akeeba.System.addClass(d,"loaded")}if(e)return}if(!akeeba.System.optionsStorage)akeeba.System.optionsStorage=a||{};else if(a)for(var h in a)a.hasOwnProperty(h)&&(akeeba.System.optionsStorage[h]=a[h])};
akeeba.System.defaultErrorHandler=function(a){null!=a&&"undefined"!=typeof a&&alert("An error has occurred\n"+a)};
akeeba.System.modalErrorHandler=function(a){var b=akeeba.System.getOptions("akeeba.System.params.errorDialogId",akeeba.System.params.errorDialogId),c=akeeba.System.getOptions("akeeba.System.params.errorDialogMessageId",akeeba.System.params.errorDialogMessageId);b=document.getElementById(b);var d="error";null!=b&&(document.getElementById(c).innerHTML=a,d=b.innerHTML);akeeba.Modal.open({content:d,width:"80%"})};
akeeba.System.doAjax=function(a,b,c,d,e){null==d&&(d=!0);var f=(new Date).getTime()/1E3,g=parseInt(String(f),10);a._cacheBustingJunk=Math.round(1E3*(f-g))/1E3;null==e&&(e=6E5);f=akeeba.System.getOptions("akeeba.System.params.AjaxURL",akeeba.System.params.AjaxURL);a.hasOwnProperty("ajaxURL")&&(f=a.ajaxURL,delete a.url);null==c&&(c=akeeba.System.getOptions("akeeba.System.params.errorCallback",akeeba.System.params.errorCallback));null==c&&(c=akeeba.System.defaultErrorHandler);a={type:"POST",url:f,cache:!1,
data:a,timeout:e,success:function(a){var d=a.indexOf("###");if(-1===d)a=akeeba.System.sanitizeErrorMessage(a),c("Invalid AJAX data: "+a);else{var e=a;0!==d&&(e=a.substr(d));e=e.substr(3);d=e.lastIndexOf("###");e=e.substr(0,d);try{var f=JSON.parse(e)}catch(k){e=akeeba.System.sanitizeErrorMessage(e);a=k.message+"\n<br/>\n<pre>\n"+e+"\n</pre>";c(a);return}b(f)}},error:function(a,b,d){d=a.responseText?a.responseText:"";var e="<strong>AJAX Loading Error</strong><br/>HTTP Status: "+a.status+" ("+a.statusText+
")<br/>";e=e+"Internal status: "+b+"<br/>XHR ReadyState: "+a.readyState+"<br/>";e=e+"Raw server response:<br/>"+akeeba.System.sanitizeErrorMessage(d);c(e)}};d?akeeba.Ajax.enqueue(a):akeeba.Ajax.ajax(a)};akeeba.System.sanitizeErrorMessage=function(a){-1<a.indexOf("<script")&&(a="(HTML containing script tags)");return a};
akeeba.System.notification.askPermission=function(){akeeba.System.getOptions("akeeba.System.notification.hasDesktopNotification",akeeba.System.notification.hasDesktopNotification)&&void 0!==window.Notification&&"default"===window.Notification.permission&&window.Notification.requestPermission()};
akeeba.System.notification.notify=function(a,b,c){void 0!==window.Notification&&"granted"===window.Notification.permission&&(void 0===c&&(c=5E3),void 0===b&&(b=""),a=new window.Notification(a,{body:b,icon:akeeba.System.getOptions("akeeba.System.notification.iconURL",akeeba.System.notification.iconURL)}),0<c&&setTimeout(function(a){return function(){a.close()}}(a),c))};
akeeba.System.data=function(){var a=0,b={};return{set:function(c,d,e){if(c.dataset)c.dataset[d]=e,null==e&&delete c.dataset[d];else{if(void 0===c.myCustomDataTag){var f=a++;c.myCustomDataTag=f}"undefined"==typeof b[f]&&(b[f]={});b[f][d]=e;var g="data-"+d.split(/(?=[A-Z])/).join("-").toLowerCase();c.setAttribute&&c.setAttribute(g,e);if(null==e)try{delete b[f][d],c.removeAttribute(g)}catch(h){b[f][d]=null}}},get:function(a,d,e){if(a.dataset)return"undefined"==typeof a.dataset[d]&&(a.dataset[d]=e),a.dataset[d];
"undefined"==typeof e&&(e=null);"undefined"==typeof b[a.myCustomDataTag]&&(b[a.myCustomDataTag]={});var c="data-"+d.split(/(?=[A-Z])/).join("-").toLowerCase();"undefined"!==typeof a[c]&&(b[a.myCustomDataTag][d]=a[c]);"undefined"==typeof b[a.myCustomDataTag][d]&&this.set(a,d,e);return b[a.myCustomDataTag][d]}}}();
akeeba.System.addEventListener=function(a,b,c){function d(a){var b=c.apply(this,arguments);!1===b&&(a.stopPropagation()&&a.stopPropagation(),a.preventDefault?a.preventDefault():a.returnValue=!1);return b}"string"===typeof a&&(a=document.getElementById(a));null!=a&&"object"==typeof a&&a instanceof Element&&(a.addEventListener?a.addEventListener(b,d,!1):a.attachEvent("on"+b,function(){var b=c.call(a,window.event);!1===b&&(window.event.returnValue=!1,window.event.cancelBubble=!0);return b}))};
akeeba.System.removeEventListener=function(a,b,c){"string"===typeof a&&(a=document.getElementById(a));null!=a&&"object"==typeof a&&a instanceof Element&&(a.removeEventListener?a.removeEventListener(b,c):a.detachEvent("on"+b,c))};
akeeba.System.triggerEvent=function(a,b){if("undefined"!=typeof a&&null!==a&&("string"===typeof a&&(a=document.getElementById(a)),"object"==typeof a&&a instanceof Element))if("function"===typeof window.jQuery)window.jQuery(a).trigger(b);else if(document.fireEvent&&"undefined"==typeof window.Event)a.fireEvent("on"+b);else{var c=document.createEvent("Event");c.initEvent(b,!0,!0);a.dispatchEvent(c)}};
(function(a,b){function c(){if(!f){f=!0;for(var a=0;a<e.length;a++)e[a].fn.call(window,e[a].ctx);e=[]}}function d(){"complete"===document.readyState&&c()}b=b||akeeba.System;var e=[],f=!1,g=!1;b[a||"documentReady"]=function(a,b){f?setTimeout(function(){a(b)},1):(e.push({fn:a,ctx:b}),"complete"===document.readyState||!document.attachEvent&&"interactive"===document.readyState?setTimeout(c,1):g||(g=!0,document.addEventListener?(document.addEventListener("DOMContentLoaded",c,!1),window.addEventListener("load",
c,!1)):(document.attachEvent("onreadystatechange",d),window.attachEvent("onload",c))))}})("documentReady",akeeba.System);akeeba.System.addClass=function(a,b){if(a&&a.className){var c=a.className.split(" ");"string"==typeof b&&(b=b.split(" "));c=akeeba.System.array_merge(c,b);a.className="";for(property in c)c.hasOwnProperty(property)&&(a.className+=c[property]+" ");a.className.trim&&(a.className=a.className.trim())}};
akeeba.System.removeClass=function(a,b){if(a&&a.className){var c=a.className.split(" ");"string"==typeof b&&(b=b.split(" "));c=akeeba.System.array_diff(c,b);a.className="";for(property in c)c.hasOwnProperty(property)&&(a.className+=c[property]+" ");a.className.trim&&(a.className=a.className.trim())}};akeeba.System.hasClass=function(a,b){if(a&&a.className){a=a.className.split(" ");for(i=0;i<a.length;i++)if(a[i]===b)return!0;return!1}};
akeeba.System.toggleClass=function(a,b){akeeba.System.hasClass(a,b)?akeeba.System.removeClass(a,b):akeeba.System.addClass(a,b)};akeeba.System.tableOrdering=function(a,b,c,d){"undefined"===typeof d&&(d=document.getElementById("adminForm"));d.filter_order.value=a;d.filter_order_Dir.value=b;akeeba.System.submitForm(c,d)};
akeeba.System.submitForm=function(a,b,c){b||(b=document.getElementById("adminForm"));a&&(b.task.value=a);b.noValidate=!c;c?b.hasAttribute("novalidate")&&b.removeAttribute("novalidate"):b.setAttribute("novalidate","");a=document.createElement("input");a.style.display="none";a.type="submit";b.appendChild(a);a.click();b.removeChild(a)};
akeeba.System.orderTable=function(){var a=document.getElementById("sortTable"),b=document.getElementById("directionTable");a=a.options[a.selectedIndex].value;var c="asc";a===akeeba.System.getOptions("akeeba.System.tableOrder","asc")&&(c=b.options[b.selectedIndex].value);akeeba.System.tableOrdering(a,c)};
akeeba.System.iterateNodes=function(a,b,c){if("function"==typeof b&&("string"===typeof a&&(a=document.querySelectorAll(a)),0!==a.length)){var d;for(d=0;d<a.length;d++){var e=a[d];"undefined"!==typeof c?b(e,c):b(e)}}};
akeeba.System.assignDefaultErrorHandler=function(){akeeba.System.params.errorCallback=akeeba.System.modalErrorHandler;if("undefined"===typeof akeeba.Modal)akeeba.System.params.errorCallback=akeeba.System.defaultErrorHandler;else{var a=akeeba.System.getOptions("akeeba.System.params.errorDialogId",akeeba.System.params.errorDialogId),b=akeeba.System.getOptions("akeeba.System.params.errorDialogMessageId",akeeba.System.params.errorDialogMessageId);if(""===a||null===a||""===b||null===b)akeeba.System.params.errorCallback=
akeeba.System.defaultErrorHandler;else if(a=document.getElementById(a),b=document.getElementById(b),null===a||null===b)akeeba.System.params.errorCallback=akeeba.System.defaultErrorHandler}};
akeeba.System.documentReady(function(){akeeba.System.assignDefaultErrorHandler();akeeba.System.iterateNodes(".akeebaGridViewCheckAll",function(a){akeeba.System.addEventListener(a,"click",function(){Joomla.checkAll(this)})});akeeba.System.iterateNodes(".akeebaGridViewOrderTable",function(a){akeeba.System.addEventListener(a,"change",akeeba.System.orderTable)});akeeba.System.iterateNodes(".akeebaGridViewAutoSubmitOnChange",function(a){akeeba.System.addEventListener(a,"change",function(){akeeba.System.submitForm()})})});
akeeba.System.Text={strings:{},_:function(a,b){["akeeba.text","joomla.jtext"].forEach(function(a){var b=akeeba.System.getOptions(a);b&&(akeeba.System.Text.load(b),b={},b[a]=null,akeeba.System.loadOptions(b))});b=void 0===b?"":b;a=a.toUpperCase();return void 0!==akeeba.System.Text.strings[a]?akeeba.System.Text.strings[a]:"undefined"!=typeof Joomla.JText?Joomla.JText._(a,b):"undefined"!=typeof Joomla.Text?Joomla.Text._(a,b):b},load:function(a){for(var b in a)a.hasOwnProperty(b)&&(this.strings[b.toUpperCase()]=
a[b]);return this}}; //# sourceMappingURL=System.min.map

// ============================================================================
// /media/com_akeeba/js/Tooltip.min.js
// ============================================================================

/*
 Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 @license   GNU General Public License version 3, or later
*/
if("undefined"==typeof akeeba)var akeeba={};"undefined"==typeof akeeba.Tooltip&&(akeeba.Tooltip={});akeeba.Tooltip.enableFor=function(a,b){if("undefined"==typeof b||null===b)b=!0;if("object"==typeof a&&NodeList.prototype.isPrototypeOf(a))for(i=0;i<a.length;i++)akeeba.Tooltip.enableFor(a[i],b);else akeeba.System.addEventListener(a,"mouseenter",akeeba.Tooltip.onMouseEnter),akeeba.System.addEventListener(a,"mouseleave",akeeba.Tooltip.onMouseLeave),b&&akeeba.System.addEventListener(a,"click",akeeba.Tooltip.onClick)};
akeeba.Tooltip.simpleTooltip=function(a){if(a.hasAttribute("title")){var b=a.getAttribute("title"),c="right";a.hasAttribute("data-akeeba-tooltip-position")&&(c=a.getAttribute("data-akeeba-tooltip-position"));akeeba.System.addClass(a,"akeeba-hasTooltip");var d=document.createElement("div");d.className="akeeba-tooltip-text akeeba-tooltip-text-"+c;d.innerHTML=b;a.appendChild(d);a.removeAttribute("title")}};
akeeba.Tooltip.onMouseEnter=function(a){if(a.target.hasAttribute("data-content")){if(a.target.hasAttribute("data-tooltip-uuid"))var b=a.target.getAttribute("data-tooltip-uuid");else b=Math.uuid(),a.target.setAttribute("data-tooltip-uuid",b);if(null==document.getElementById("akeeba-tooltip-"+b)){var c=a.target.getAttribute("data-position")||"center bottom",d=c.split(" ")[0],e=c.split(" ")[1],f=document.createElement("div");f.className="akeeba-popover";f.id="akeeba-tooltip-"+b;f.style.display="block";
f.className+=" "+c;f.insertAdjacentHTML("afterbegin",'<div class="arrow"></div>');b=document.createElement("div");b.className="akeeba-popover-inner";f.appendChild(b);a.target.hasAttribute("data-original-title")&&(c=document.createElement("h3"),c.className="akeeba-popover-title",c.innerHTML=a.target.getAttribute("data-original-title"),b.appendChild(c));c=document.createElement("div");c.className="akeeba-popover-content";c.innerHTML=a.target.getAttribute("data-content");b.appendChild(c);document.body.appendChild(f);
akeeba.Tooltip.positionAt(a.target,f,d,e)}}};akeeba.Tooltip.onMouseLeave=function(a){a.target.hasAttribute("data-tooltip-noclose")||akeeba.Tooltip.hideTooltip(a.target)};akeeba.Tooltip.onClick=function(a){if(a.target.hasAttribute("data-tooltip-uuid"))if(a.target.hasAttribute("data-tooltip-noclose"))a.target.removeAttribute("data-tooltip-noclose");else{var b=a.target.getAttribute("data-tooltip-uuid");null!=document.getElementById("akeeba-tooltip-"+b)&&a.target.setAttribute("data-tooltip-noclose",1)}};
akeeba.Tooltip.hideTooltip=function(a){a.hasAttribute("data-tooltip-uuid")&&(a=a.getAttribute("data-tooltip-uuid"),(a=document.getElementById("akeeba-tooltip-"+a))&&document.body.removeChild(a))};
akeeba.Tooltip.positionAt=function(a,b,c,d){var e=a.getBoundingClientRect();switch(c){case "left":a=parseInt(e.left)-10-b.offsetWidth;0>parseInt(e.left)-b.offsetWidth&&(a=10);break;case "right":a=e.right+10;parseInt(e.right)+b.offsetWidth>document.documentElement.clientWidth&&(a=document.documentElement.clientWidth-b.offsetWidth-10);break;default:case "center":a=parseInt(e.left)+(a.offsetWidth-b.offsetWidth)/2}switch(d){case "center":d=(parseInt(e.top)+parseInt(e.bottom))/2-b.offsetHeight/2;break;
case "bottom":d=parseInt(e.bottom)+10;break;default:case "top":d=parseInt(e.top)-b.offsetHeight-10}a=0>a?parseInt(e.left):a;d=0>d?parseInt(e.bottom)+10:d;b.style.left=a+"px";b.style.top=d+pageYOffset+"px"}; //# sourceMappingURL=Tooltip.min.map

// ============================================================================
// /media/com_akeeba/js/piecon.min.js
// ============================================================================

/*
 piecon.js

 https://github.com/lipka/piecon

 Copyright (c) 2015 Lukas Lipka <lukaslipka@gmail.com>. All rights reserved.
*/
var $jscomp=$jscomp||{};$jscomp.scope={};$jscomp.ASSUME_ES5=!1;$jscomp.ASSUME_NO_NATIVE_MAP=!1;$jscomp.ASSUME_NO_NATIVE_SET=!1;$jscomp.SIMPLE_FROUND_POLYFILL=!1;$jscomp.defineProperty=$jscomp.ASSUME_ES5||"function"==typeof Object.defineProperties?Object.defineProperty:function(b,g,c){b!=Array.prototype&&b!=Object.prototype&&(b[g]=c.value)};$jscomp.getGlobal=function(b){return"undefined"!=typeof window&&window===b?b:"undefined"!=typeof global&&null!=global?global:b};$jscomp.global=$jscomp.getGlobal(this);
$jscomp.polyfill=function(b,g,c,d){if(g){c=$jscomp.global;b=b.split(".");for(d=0;d<b.length-1;d++){var f=b[d];f in c||(c[f]={});c=c[f]}b=b[b.length-1];d=c[b];g=g(d);g!=d&&null!=g&&$jscomp.defineProperty(c,b,{configurable:!0,writable:!0,value:g})}};$jscomp.polyfill("Array.prototype.fill",function(b){return b?b:function(b,c,d){var f=this.length||0;0>c&&(c=Math.max(0,f+c));if(null==d||d>f)d=f;d=Number(d);0>d&&(d=Math.max(0,f+d));for(c=Number(c||0);c<d;c++)this[c]=b;return this}},"es6","es3");
(function(){var b={},g=null,c=null,d=null,f=null,k={},l={color:"#ff0084",background:"#bbb",shadow:"#fff",fallback:!1},p=1<window.devicePixelRatio,h=function(){var b=navigator.userAgent.toLowerCase();return function(a){return-1!==b.indexOf(a)}}(),q=h("msie");h("chrome");h("chrome")||h("safari");var r=h("safari")&&!h("chrome");!h("mozilla")||h("chrome")||h("safari");var m=function(b){for(var a=Array.prototype.slice.call(document.getElementsByTagName("link"),0),d=document.getElementsByTagName("head")[0],
c=0,f=a.length;c<f;c++)"icon"!==a[c].getAttribute("rel")&&"shortcut icon"!==a[c].getAttribute("rel")||d.removeChild(a[c]);a=document.createElement("link");a.type="image/x-icon";a.rel="icon";a.href=b;document.getElementsByTagName("head")[0].appendChild(a)},n=function(){f||(f=document.createElement("canvas"),p?(f.width=32,f.height=32):(f.width=16,f.height=16));return f};b.setOptions=function(b){k={};for(var a in l)k[a]=b.hasOwnProperty(a)?b[a]:l[a];return this};b.setProgress=function(b){d||(d=document.title);
if(!c||!g){a:{var a=document.getElementsByTagName("link");for(var e=0,f=a.length;e<f;e++)if("icon"===a[e].getAttribute("rel")||"shortcut icon"===a[e].getAttribute("rel")){a=a[e];break a}a=!1}c=g=a?a.getAttribute("href"):"/favicon.ico"}if(!isNaN(parseFloat(b))&&isFinite(b))!n().getContext||q||r||!0===k.fallback?document.title=0<b?"("+b+"%) "+d:d:("force"===k.fallback&&(document.title=0<b?"("+b+"%) "+d:d),a=n(),e=a.getContext("2d"),b=b||0,e&&(e.clearRect(0,0,a.width,a.height),e.beginPath(),e.moveTo(a.width/
2,a.height/2),e.arc(a.width/2,a.height/2,Math.min(a.width/2,a.height/2),0,2*Math.PI,!1),e.fillStyle=k.shadow,e.fill(),e.beginPath(),e.moveTo(a.width/2,a.height/2),e.arc(a.width/2,a.height/2,Math.min(a.width/2,a.height/2)-2,0,2*Math.PI,!1),e.fillStyle=k.background,e.fill(),0<b&&(e.beginPath(),e.moveTo(a.width/2,a.height/2),e.arc(a.width/2,a.height/2,Math.min(a.width/2,a.height/2)-2,-.5*Math.PI,(-.5+2*b/100)*Math.PI,!1),e.lineTo(a.width/2,a.height/2),e.fillStyle=k.color,e.fill()),m(a.toDataURL())));
else return!1};b.reset=function(){d&&(document.title=d);c&&(g=c,m(g))};b.setOptions(l);"function"===typeof define&&define.amd?define(b):"undefined"!==typeof module?module.exports=b:window.Piecon=b})(); //# sourceMappingURL=piecon.min.map

// ============================================================================
// /media/com_akeeba/js/ControlPanel.min.js
// ============================================================================

/*
 Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 @license   GNU General Public License version 3, or later
*/
if("undefined"==typeof akeeba)var akeeba={};"undefined"==typeof akeeba.ControlPanel&&(akeeba.ControlPanel={needsDownloadID:!0,outputDirUnderSiteRoot:!1,hasSecurityFiles:!1});akeeba.ControlPanel.showChangelog=function(){akeeba.Modal.open({inherit:"#akeeba-changelog",width:"80%"})};
akeeba.ControlPanel.showUpdateInformation=function(){akeeba.System.getOptions("akeeba.ControlPanel.needsDownloadID",!1)||akeeba.Ajax.ajax("index.php?option=com_akeeba&view=ControlPanel&task=UpdateInfo&tmpl=component",{success:function(a){data=a.match(/###([\s\S]*?)###/)[1];data.length&&(document.getElementById("updateNotice").innerHTML=data)}})};
akeeba.ControlPanel.checkOutputFolderSecurity=function(){akeeba.System.getOptions("akeeba.ControlPanel.outputDirUnderSiteRoot",!1)&&akeeba.System.doAjax({ajaxURL:"index.php?option=com_akeeba&view=ControlPanel&task=checkOutputDirectory&format=raw"},function(a){var c=a.hasOwnProperty("readFile")?a.readFile:!1,b=a.hasOwnProperty("listFolder")?a.listFolder:!1,d=a.hasOwnProperty("isSystem")?a.isSystem:!1;a=a.hasOwnProperty("hasRandom")?a.hasRandom:!0;b&&d?document.getElementById("outDirSystem").style.display=
"block":b?document.getElementById("insecureOutputDirectory").style.display="block":!c||b||a||(akeeba.System.getOptions("akeeba.ControlPanel.hasSecurityFiles",!0)?a||(document.getElementById("missingRandomFromFilename").style.display="block"):document.getElementById("insecureOutputDirectory").style.display="block")},function(a){},!1)};
akeeba.System.documentReady(function(){akeeba.System.addEventListener("comAkeebaControlPanelProfileSwitch","change",function(){document.forms.switchActiveProfileForm.submit()});akeeba.System.addEventListener(document.getElementById("btnchangelog"),"click",akeeba.ControlPanel.showChangelog);akeeba.System.notification.askPermission();akeeba.ControlPanel.showUpdateInformation();akeeba.ControlPanel.checkOutputFolderSecurity();var a=document.getElementById("notfixedperms");null!==a&&a.parentElement.removeChild(a)}); //# sourceMappingURL=ControlPanel.min.map

