/*
WYSIWYG-BBCODE editor
Copyright (c) 2009, Jitbit Sotware, http://www.jitbit.com/
PROJECT HOME: http://wysiwygbbcode.codeplex.com/
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
	* Redistributions of source code must retain the above copyright
	  notice, this list of conditions and the following disclaimer.
	* Redistributions in binary form must reproduce the above copyright
	  notice, this list of conditions and the following disclaimer in the
	  documentation and/or other materials provided with the distribution.
	* Neither the name of the <organization> nor the
	  names of its contributors may be used to endorse or promote products
	  derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY Jitbit Software ''AS IS'' AND ANY
EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL Jitbit Software BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/


function obssShowTags(el){
	elclass = el.getAttribute('class');
	if( elclass == 'obss-insert-tag-btn-wrap' ) {
		el.setAttribute('class','obss-insert-tag-btn-wrap-active');
	} else if ( elclass == 'obss-insert-tag-btn-wrap-active' ) {
		el.setAttribute('class','obss-insert-tag-btn-wrap');
	}
}


MyEditor = new function( ){
	var editors = {};

	this.initEditor = function( textarea_id, wysiwyg, assets_path ) {
		//console.log(editors.hasOwnProperty(textarea_id));
		if( !editors.hasOwnProperty(textarea_id) ) {
			editors[textarea_id] = new wswgEditor();
			editors[textarea_id].initEditor( textarea_id, wysiwyg );
		}
	}

	this.doAction = function ( textarea_id, fname, arg ) {
		var editor = this.getEditor(textarea_id);
		//console.log(editor);
		if( arg ) {
			eval('editor.'+fname+'("'+arg+'")');
		} else {
			eval('editor.'+fname+'()');
		}
	}
	
	this.getEditor = function( textarea_id ) {
		//console.log(editors.hasOwnProperty(textarea_id));
		if(editors.hasOwnProperty( textarea_id )){
			return editors[textarea_id];
		}
		return false;
	}
}

function wswgEditor() {

	this.getEditorDoc = function () { return myeditor; }
	this.getIframe = function () { return ifm; }
	this.IsEditorVisible = function () { return editorVisible; }

	var myeditor, ifm;
	var body_id, textboxelement;
	var content;
	var isIE = /msie|MSIE/.test(navigator.userAgent);
	var isChrome = /Chrome/.test(navigator.userAgent);
	var isSafari = /Safari/.test(navigator.userAgent) && !isChrome;
	var browser = isIE || window.opera;
	var textRange;
	var editorVisible = false;
	var enableWysiwyg = false;

	function rep(re, str) {
		content = content.replace(re, str);
	}

	this.initEditor = function (textarea_id, wysiwyg) {
		if (wysiwyg != undefined)
			enableWysiwyg = wysiwyg;
		else
			enableWysiwyg = true;
		body_id = textarea_id;
		textboxelement = document.getElementById(body_id);
		textboxelement.setAttribute('class', 'editorBBCODE');
		textboxelement.className = "editorBBCODE";
		if (enableWysiwyg) {
			if (!document.getElementById("rte"+textarea_id)) { //to prevent recreation
				ifm = document.createElement("iframe");
				ifm.setAttribute("id", "rte"+textarea_id);
				ifm.setAttribute("frameBorder", "0");
				ifm.style.width = textboxelement.style.width;
				ifm.style.height = textboxelement.style.height;
				textboxelement.parentNode.insertBefore(ifm, textboxelement);
				textboxelement.style.display = 'none';
			}
			if (ifm) {
				this.InitIframe();
			} else
				setTimeout('InitIframe()', 100);
		}
	}

	this.InitIframe = function() {
		myeditor = ifm.contentWindow.document;
		myeditor.designMode = "on";
		myeditor.open();
		//myeditor.write('<html><head><link href="editor.css" rel="Stylesheet" type="text/css" /></head>');
		myeditor.write('<html><head></head>');
		myeditor.write('<body style="margin:0px 0px 0px 0px" class="editorWYSIWYG">');
		myeditor.write('</body></html>');
		myeditor.close();
		myeditor.body.contentEditable = true;
		ifm.contentEditable = true;
		if (myeditor.attachEvent) {
			myeditor.attachEvent("onkeypress", kp);
			myeditor.attachEvent("onkeypress", kp);
			console.log(kb);
		} else if (myeditor.addEventListener) {
			myeditor.addEventListener("keypress", kp, true);
			// auto run doCheck when blur
			ifm.contentWindow.addEventListener("blur", function(myeditor){
					MyEditor.doAction(ifm.id.substr(3),'doCheck');
				}, false);
		}
		//wswgEditor.ShowEditor();
		this.ShowEditor();
	}

	this.ShowEditor = function () {
		if (!enableWysiwyg) return;
		editorVisible = true;
		content = document.getElementById(body_id).value;
		bbcode2html();
		myeditor.body.innerHTML = content;
	}

	this.SwitchEditor = function () {
		if (editorVisible) {
			this.doCheck();
			ifm.style.display = 'none';
			textboxelement.style.display = '';
			editorVisible = false;
			textboxelement.focus();
		}
		else {
			if (enableWysiwyg && ifm) {
				ifm.style.display = '';
				textboxelement.style.display = 'none';
				this.ShowEditor();
				editorVisible = true;
				ifm.contentWindow.focus();
			}
		}
	}

	function html2bbcode() {
		rep(/<input class="obss-tag" type="button" value="([^"]+)"[^>]*>/gi, "$1");
		rep(/<input class="obss-tag" value="(\[[^\]]+\])" type="button">/gi, "$1");
		rep(/<img\s[^<>]*?src=\"?([^<>]*?)\"?(\s[^<>]*)?\/?>/gi, "[img]$1[/img]");
		rep(/<\/(strong|b)>/gi, "[/b]");
		rep(/<(strong|b)(\s[^<>]*)?>/gi, "[b]");
		rep(/<\/(em|i)>/gi, "[/i]");
		rep(/<(em|i)(\s[^<>]*)?>/gi, "[i]");
		rep(/<\/u>/gi, "[/u]");
		rep(/\n/gi, " ");
		rep(/\r/gi, " ");
		rep(/<u(\s[^<>]*)?>/gi, "[u]");
		rep(/<div><br(\s[^<>]*)?>/gi, "<div>"); //chrome-safari fix to prevent double linefeeds
		rep(/<br(\s[^<>]*)?>/gi, "\n");
		rep(/<p(\s[^<>]*)?>/gi, "");
		rep(/<\/p>/gi, "\n");
		rep(/<ul>/gi, "[ul]");
		rep(/<\/ul>/gi, "[/ul]");
		rep(/<ol>/gi, "[ol]");
		rep(/<\/ol>/gi, "[/ol]");
		rep(/<li>/gi, "[li]");
		rep(/<\/li>/gi, "[/li]");
		rep(/<\/div>\s*<div([^<>]*)>/gi, "</span>\n<span$1>"); //chrome-safari fix to prevent double linefeeds
		rep(/<div([^<>]*)>/gi, "\n<span$1>");
		rep(/<\/div>/gi, "</span>\n");

		rep(/<table([^<>]*)>/gi, "[table]");
		rep(/<\/table>/gi, "[/table]");
		rep(/<tr([^<>]*)>/gi, "[tr]");
		rep(/<tr([^<>]*)>/gi, "[tr]");
		rep(/<td([^<>]*)>/gi, "[td]");
		rep(/<td([^<>]*)>/gi, "[td]");

		rep(/&nbsp;/gi, " ");
		rep(/&quot;/gi, "\"");
		rep(/&amp;/gi, "&");

		//remove style & script tags
		rep(/<script.*?>[\s\S]*?<\/script>/gi, "");
		rep(/<style.*?>[\s\S]*?<\/style>/gi, "");

		//remove [if] blocks (when pasted from outlook etc)
		rep(/<!--\[if[\s\S]*?<!\[endif\]-->/gi, "");

		var sc, sc2;
		do {
			sc = content;
			rep(/<font\s[^<>]*?color=\"?([^<>]*?)\"?(\s[^<>]*)?>([^<>]*?)<\/font>/gi, "[color=$1]$3[/color]");
			if (sc == content)
				rep(/<font[^<>]*>([^<>]*?)<\/font>/gi, "$1");
			rep(/<a\s[^<>]*?href=\"?([^<>]*?)\"?(\s[^<>]*)?>([^<>]*?)<\/a>/gi, "[url=$1]$3[/url]");
			sc2 = content;
			rep(/<(span|blockquote|pre)\s[^<>]*?style=\"?font-weight: ?bold;?\"?\s*([^<]*?)<\/\1>/gi, "[b]<$1 style=$2</$1>[/b]");
			rep(/<(span|blockquote|pre)\s[^<>]*?style=\"?font-weight: ?normal;?\"?\s*([^<]*?)<\/\1>/gi, "<$1 style=$2</$1>");
			rep(/<(span|blockquote|pre)\s[^<>]*?style=\"?font-style: ?italic;?\"?\s*([^<]*?)<\/\1>/gi, "[i]<$1 style=$2</$1>[/i]");
			rep(/<(span|blockquote|pre)\s[^<>]*?style=\"?font-style: ?normal;?\"?\s*([^<]*?)<\/\1>/gi, "<$1 style=$2</$1>");
			rep(/<(span|blockquote|pre)\s[^<>]*?style=\"?text-decoration: ?underline;?\"?\s*([^<]*?)<\/\1>/gi, "[u]<$1 style=$2</$1>[/u]");
			rep(/<(span|blockquote|pre)\s[^<>]*?style=\"?text-decoration: ?none;?\"?\s*([^<]*?)<\/\1>/gi, "<$1 style=$2</$1>");
			rep(/<(span|blockquote|pre)\s[^<>]*?style=\"?color: ?([^<>]*?);\"?\s*([^<]*?)<\/\1>/gi, "[color=$2]<$1 style=$3</$1>[/color]");
			rep(/<(span|blockquote|pre)\s[^<>]*?style=\"?font-family: ?([^<>]*?);\"?\s*([^<]*?)<\/\1>/gi, "[font=$2]<$1 style=$3</$1>[/font]");
			rep(/<(blockquote|pre)\s[^<>]*?style=\"?\"? (class=|id=)([^<>]*)>([^<>]*?)<\/\1>/gi, "<$1 $2$3>$4</$1>");
			rep(/<pre>([^<>]*?)<\/pre>/gi, "[code]$1[/code]");
			rep(/<span\s[^<>]*?style=\"?\"?>([^<>]*?)<\/span>/gi, "$1");
			if (sc2 == content) {
				rep(/<span[^<>]*>([^<>]*?)<\/span>/gi, "$1");
				sc2 = content;
			}
		} while (sc != content)
		rep(/<[^<>]*>/gi, "");
		rep(/&lt;/gi, "<");
		rep(/&gt;/gi, ">");

		do {
			sc = content;
			rep(/\[(b|i|u)\]\[quote([^\]]*)\]([\s\S]*?)\[\/quote\]\[\/\1\]/gi, "[quote$2][$1]$3[/$1][/quote]");
			rep(/\[color=([^\]]*)\]\[quote([^\]]*)\]([\s\S]*?)\[\/quote\]\[\/color\]/gi, "[quote$2][color=$1]$3[/color][/quote]");
			rep(/\[(b|i|u)\]\[code\]([\s\S]*?)\[\/code\]\[\/\1\]/gi, "[code][$1]$2[/$1][/code]");
			rep(/\[color=([^\]]*)\]\[code\]([\s\S]*?)\[\/code\]\[\/color\]/gi, "[code][color=$1]$2[/color][/code]");
		} while (sc != content)

		//clean up empty tags
		do {
			sc = content;
			rep(/\[b\]\[\/b\]/gi, "");
			rep(/\[i\]\[\/i\]/gi, "");
			rep(/\[u\]\[\/u\]/gi, "");
			rep(/\[quote[^\]]*\]\[\/quote\]/gi, "");
			rep(/\[code\]\[\/code\]/gi, "");
			rep(/\[url=([^\]]+)\]\[\/url\]/gi, "");
			rep(/\[img\]\[\/img\]/gi, "");
			rep(/\[color=([^\]]*)\]\[\/color\]/gi, "");
		} while (sc != content)
	}

	function bbcode2html() {
		// example: [b] to <strong>
		rep(/\</gi, "&lt;"); //removing html tags
		rep(/\>/gi, "&gt;");

		rep(/\n/gi, "<br />");
		rep(/\[ul\]/gi, "<ul>");
		rep(/\[\/ul\]/gi, "</ul>");
		rep(/\[ol\]/gi, "<ol>");
		rep(/\[\/ol\]/gi, "</ol>");
		rep(/\[li\]/gi, "<li>");
		rep(/\[\/li\]/gi, "</li>");

		rep(/\[table\]/gi, "<table border='1'>");
		rep(/\[\/table\]/gi, "</table>");
		rep(/\[tr\]/gi, "<tr>");
		rep(/\[\/tr\]/gi, "</tr>");
		rep(/\[td\]/gi, "<td>");
		rep(/\[\/td\]/gi, "</td>");

		if (browser) {
			rep(/\[b\]/gi, "<strong>");
			rep(/\[\/b\]/gi, "</strong>");
			rep(/\[i\]/gi, "<em>");
			rep(/\[\/i\]/gi, "</em>");
			rep(/\[u\]/gi, "<u>");
			rep(/\[\/u\]/gi, "</u>");
		} else {
			rep(/\[b\]/gi, "<span style=\"font-weight: bold;\">");
			rep(/\[i\]/gi, "<span style=\"font-style: italic;\">");
			rep(/\[u\]/gi, "<span style=\"text-decoration: underline;\">");
			rep(/\[\/(b|i|u)\]/gi, "</span>");
		}
		rep(/\[img\]([^\"]*?)\[\/img\]/gi, "<img src=\"$1\" />");
		var sc;
		do {
			sc = content;
			rep(/\[url=([^\]]+)\]([\s\S]*?)\[\/url\]/gi, "<a href=\"$1\">$2</a>");
			rep(/\[url\]([\s\S]*?)\[\/url\]/gi, "<a href=\"$1\">$1</a>");
			if (browser) {
				rep(/\[color=([^\]]*?)\]([\s\S]*?)\[\/color\]/gi, "<font color=\"$1\">$2</font>");
				rep(/\[font=([^\]]*?)\]([\s\S]*?)\[\/font\]/gi, "<font face=\"$1\">$2</font>");
			} else {
				rep(/\[color=([^\]]*?)\]([\s\S]*?)\[\/color\]/gi, "<span style=\"color: $1;\">$2</span>");
				rep(/\[font=([^\]]*?)\]([\s\S]*?)\[\/font\]/gi, "<span style=\"font-family: $1;\">$2</span>");
			}
			rep(/\[code\]([\s\S]*?)\[\/code\]/gi, "<pre>$1</pre>&nbsp;");
		} while (sc != content);
		rep(/(\[[^\[\]]+\])/gi, '<input class="obss-tag" type="button" value="$1"/>');
	}

	this.doCheck = function () {
		if (!enableWysiwyg) return;
		if (!editorVisible) {
			this.ShowEditor();
		}
		content = myeditor.body.innerHTML;
		html2bbcode();
		document.getElementById(body_id).value = content;
	}

	function stopEvent(evt) {
		evt || window.event;
		if (evt.stopPropagation) {
			evt.stopPropagation();
			evt.preventDefault();
		} else if (typeof evt.cancelBubble != "undefined") {
			evt.cancelBubble = true;
			evt.returnValue = false;
		}
		return false;
	}

	this.doQuote = function () {
		if (editorVisible) {
			ifm.contentWindow.focus();
			if (isIE) {
				textRange = ifm.contentWindow.document.selection.createRange();
				var newTxt = "[quote=]" + textRange.text + "[/quote]";
				textRange.text = newTxt;
			}
			else {
				var edittext = ifm.contentWindow.getSelection().getRangeAt(0);
				var original = edittext.toString();
				edittext.deleteContents();
				edittext.insertNode(ifm.contentWindow.document.createTextNode("[quote=]" + original + "[/quote]"));
			}
		}
		else {
			AddTag('[quote=]', '[/quote]');
		}
	}

	function kp(e) {
		if (isIE) {
			if (e.keyCode == 13) {
				var r = myeditor.selection.createRange();
				if (r.parentElement().tagName.toLowerCase() != "li") {
					r.pasteHTML('<br/>');
					if (r.move('character'))
						r.move('character', -1);
					r.select();
					stopEvent(e);
					return false;
				}
			}
		}
	}
	this.InsertYoutube = function () {
		this.InsertText(" http://www.youtube.com/watch?v=XXXXXXXXXXX ");
	}	
	this.InsertText = function (txt) {
		if (editorVisible)
			insertHtml(txt);
		else
			textboxelement.value += txt;
	}

	this.doClick = function (command) {
		if (editorVisible) {
			ifm.contentWindow.focus();
			myeditor.execCommand(command, false, null);
		}
		else {
			switch (command) {
				case 'bold':
					AddTag('[b]', '[/b]'); break;
				case 'italic':
					AddTag('[i]', '[/i]'); break;
				case 'underline':
					AddTag('[u]', '[/u]'); break;
				case 'InsertUnorderedList':
					AddTag('[ul][li]', '[/li][/ul]'); break;
			}
		}
	}

	function doColor(color) {
		ifm.contentWindow.focus();
		if (isIE) {
			textRange = ifm.contentWindow.document.selection.createRange();
			textRange.select();
		}
		myeditor.execCommand('forecolor', false, color);
	}

	this.doLink = function () {
		if (editorVisible) {
			ifm.contentWindow.focus();
			var mylink = prompt("Enter a URL:", "http://");
			if ((mylink != null) && (mylink != "")) {
				if (isIE) { //IE
					var range = ifm.contentWindow.document.selection.createRange();
					if (range.text == '') {
						range.pasteHTML("<a href='" + mylink + "'>" + mylink + "</a>");
					}
					else
						myeditor.execCommand("CreateLink", false, mylink);
				}
				else if (window.getSelection) { //FF
					var userSelection = ifm.contentWindow.getSelection().getRangeAt(0);
					if (userSelection.toString().length == 0)
						myeditor.execCommand('inserthtml', false, "<a href='" + mylink + "'>" + mylink + "</a>");
					else
						myeditor.execCommand("CreateLink", false, mylink);
				}
				else
					myeditor.execCommand("CreateLink", false, mylink);
			}
		}
		else {
			AddTag('[url=', ']click here[/url]');
		}
	}
	this.doImage = function () {
		if (editorVisible) {
			ifm.contentWindow.focus();
			myimg = prompt('Enter Image URL:', 'http://');
			if ((myimg != null) && (myimg != "")) {
				myeditor.execCommand('InsertImage', false, myimg);
			}
		}
		else {
			AddTag('[img]', '[/img]');
		}
	}

	function insertHtml(html) {
		if (window.getSelection) { // IE9 and non-IE
			var sel = ifm.contentWindow.getSelection();
			if (sel.getRangeAt && sel.rangeCount) {
				var range = sel.getRangeAt(0);
				range.deleteContents();

				var el = ifm.contentWindow.document.createElement("div");
				el.innerHTML = html;
				var frag = ifm.contentWindow.document.createDocumentFragment(), node, lastNode;
				while ((node = el.firstChild)) {
					lastNode = frag.appendChild(node);
				}
				range.insertNode(frag);
			}
		} else if (ifm.contentWindow.document.selection && ifm.contentWindow.document.selection.type != "Control") {
			// IE < 9
			ifm.contentWindow.document.selection.createRange().pasteHTML(html);
		}
	}

	//textarea-mode functions
	function MozillaInsertText(element, text, pos) {
		element.value = element.value.slice(0, pos) + text + element.value.slice(pos);
	}

	function AddTag(t1, t2) {
		var element = textboxelement;
		if (isIE) {
			if (document.selection) {
				element.focus();

				var txt = element.value;
				var str = document.selection.createRange();

				if (str.text == "") {
					str.text = t1 + t2;
				}
				else if (txt.indexOf(str.text) >= 0) {
					str.text = t1 + str.text + t2;
				}
				else {
					element.value = txt + t1 + t2;
				}
				str.select();
			}
		}
		else if (typeof (element.selectionStart) != 'undefined') {
			var sel_start = element.selectionStart;
			var sel_end = element.selectionEnd;
			MozillaInsertText(element, t1, sel_start);
			MozillaInsertText(element, t2, sel_end + t1.length);
			element.selectionStart = sel_start;
			element.selectionEnd = sel_end + t1.length + t2.length;
			element.focus();
		}
		else {
			element.value = element.value + t1 + t2;
		}
	}
	
	this.InsertTag = function (tagtext) {
		if (editorVisible) {
			ifm.contentWindow.focus();
			//var mylink = prompt("Enter a URL:", "http://");
			if ((tagtext != null) && (tagtext != "")) {
				if (isIE) { //IE
					var range = ifm.contentWindow.document.selection.createRange();
					if (range.text == '') {
						range.pasteHTML( tagtext );
					}
					else
						myeditor.execCommand("CreateLink", false, tagtext);
				}
				else if (window.getSelection) { //FF
					var userSelection = ifm.contentWindow.getSelection().getRangeAt(0);
					if (userSelection.toString().length == 0) {
						myeditor.execCommand('inserthtml', false, '<input class="obss-tag" type="button" value="'+tagtext+'"/>');
					} else {
						myeditor.execCommand("delete");
						myeditor.execCommand('inserthtml', false, '<input class="obss-tag" type="button" value="'+tagtext+'"/>');
					}
				}
				else {
					myeditor.execCommand("inserthtml", false, '<input class="obss-tag" type="button" value="'+tagtext+'"/>');
				}
			}
		}
		else {
			insertTagText(tagtext);
		}
	}

	function insertTagText(tagtext) {
		var element = textboxelement;
		if (isIE) {
			if (document.selection) {
				element.focus();

				var txt = element.value;
				var str = document.selection.createRange();

				if (str.text == "") {
					str.text = tagtext;
				}
				/*
				else if (txt.indexOf(str.text) >= 0) {
					str.text = t1 + str.text + t2;
				}*/
				else {
					element.value = txt + tagtext;
				}
				str.select();
			}
		}
		else if (typeof (element.selectionStart) != 'undefined') {
			var sel_start 	= element.selectionStart;
			var sel_end 	= element.selectionEnd;
			if(sel_start!=sel_end){
				t1 = (element.value).substr(0,sel_start);
				t2 = (element.value).substr(sel_end);
				element.value = t1+t2;
				sel_end = sel_start
			}
			
			MozillaInsertText(element, tagtext, sel_start);
			
			//MozillaInsertText(element, t2, sel_end + t1.length);
			element.selectionStart = sel_end + tagtext.length;
			element.selectionEnd = sel_end + tagtext.length;
			element.focus();
		}
		else {
			element.value = element.value + tagtext;
		}
	}
	
}