/* jce - 2.6.8 | 2017-02-01 | http://www.joomlacontenteditor.net | Copyright (C) 2006 - 2017 Ryan Demmer. All rights reserved | GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html */
!function($){var openwith={googledocs:{supported:["doc","docx","xls","xlsx","ppt","pptx","pdf","pages","ai","psd","tiff","dxf","svg","ps","ttf","xps","rar"],link:"https://docs.google.com/viewer?url=",embed:"https://docs.google.com/viewer?embedded=true&url="},officeapps:{supported:["doc","docx","xls","xlsx","ppt","pptx"],link:"https://view.officeapps.live.com/op/view.aspx?src=",embed:"https://view.officeapps.live.com/op/embed.aspx?src="}},FileManager={init:function(){function disableOptions(){$(".filemanager-link-options").find(":input").not("#target").prop("disabled",!0),$("#layout").sortable("destroy")}tinyMCEPopup.restoreSelection();var el,ed=tinyMCEPopup.editor,se=ed.selection,n=se.getNode(),self=this,href="";if($("#insert").click(function(e){self.insert(),e.preventDefault()}),TinyMCE_Utils.fillClassList("date_class"),TinyMCE_Utils.fillClassList("size_class"),TinyMCE_Utils.fillClassList("classlist"),el=ed.dom.getParent(n,"A")||ed.dom.getParent(n,"IMG"),this._setupSortables(),Wf.init(),WFPopups.setup(),$("#format").change(function(){var state="embed"===this.value;$(".filemanager-link-options").toggle(!state),$(".filemanager-embed-options").toggle(state).find(":input[required]").prop("disabled",!state),$(".format-link").toggle(!state).find(":input").prop("disabled",state)}).change(),$("#format_openwith").change(function(){$('option[value="download"]',"#target").prop("disabled",""!==this.value)}),el&&ed.dom.is(el,".jce_file, .wf_file, .mceItemIframe")){ed.selection.select(el),$(".uk-button-text","#insert").text(tinyMCEPopup.getLang("update","Update",!0));var cls=ed.dom.getAttrib(el,"class");if(cls=tinymce.trim(cls.replace(/mceItem([a-z]+)/gi,"").replace(/(wf|jce)_file/gi,"")),$("#classes").val(cls).change(),ed.dom.is(el,".mceItemIframe")){var data=$.parseJSON(ed.dom.getAttrib(n,"data-mce-json"));data&&data.iframe&&$.each(data.iframe,function(k,v){$("#"+k).is(":checkbox")?$("#"+k).prop("checked",!!v):("src"==k&&(v=v.replace(/http:\/\//,"https://"),$.each(openwith,function(ow,ov){var match,link=ov.link,embed=ov.embed;if(v.indexOf(link)!==-1&&(v=v.substring(link.length),match=!0),v.indexOf(embed)!==-1&&(v=v.substring(embed.length),match=!0),match)return $("#format_openwith").val(ow),!0}),v=ed.convertURL(decodeURIComponent(v)),k="href"),$("#"+k).val(v))}),$.each(["width","height"],function(i,k){var v=ed.dom.getAttrib(el,k);$("#"+k).val(v).data("tmp",v).change()}),$("#format").val("embed").change()}else{href=ed.dom.getAttrib(el,"href"),$.each(openwith,function(k,v){var match,link=v.link,embed=v.embed;if(href.indexOf(link)!==-1&&(href=href.substring(link.length),match=!0),href.indexOf(embed)!==-1&&(href=href.substring(embed.length),match=!0),match)return $("#format_openwith").val(k),!0}),href=ed.convertURL(decodeURIComponent(href)),$("#href").val(href),$.each(["title","id","style","dir","lang","tabindex","accesskey","class","charset","hreflang","target","rev"],function(i,k){$("#"+k).val(ed.dom.getAttrib(el,k)).change()});var options=$("#layout > div"),ordered=[];$.each(el.childNodes,function(i,n){switch(n.nodeName){case"IMG":ed.dom.is(n,".jce_icon, .wf_file_icon")?($("#layout_icon_check").prop("checked",!0),ordered.push($("#layout_icon").get(0))):disableOptions();break;case"#text":/[\w]+/i.test(n.data)&&($("#layout_text_check").prop("checked",!0),$("#text").val(n.data),ordered.push($("#layout_text").get(0)));break;case"SPAN":var v=tinymce.trim(n.innerHTML),cls=n.className.replace(/(wf|jce)_(file_)?(text|size|date)/i,"");ed.dom.is(n,".wf_file_text")&&($("#layout_text_check").prop("checked",!0),$("#text").val(v),ordered.push($("#layout_text").get(0))),ed.dom.is(n,".jce_size, .jce_file_size, .wf_file_size")&&($("input:text","#layout_size").val(v),$("input:checkbox","#layout_size").prop("checked",!0),$("#size_class").val(tinymce.trim(cls)),ordered.push($("#layout_size").get(0))),ed.dom.is(n,".jce_date, .jce_file_date, .wf_file_date")&&($("input:text","#layout_date").val(v),$("input:checkbox","#layout_size").prop("checked",!0),$("#date_class").val(tinymce.trim(cls)),ordered.push($("#layout_date").get(0)))}}),ordered.length<options.length&&$.each(options,function(i,n){ordered.indexOf(n)==-1&&ordered.splice(i,0,n)}),$("#layout").empty().append(ordered)}WFPopups.getPopup(n)}else{var n=se.getNode();if(n&&n!=ed.getBody()){for(;n&&ed.dom.isBlock(n);)n=n.firstChild;if(n&&(3==n.nodeType||ed.dom.is(n,"img,b,strong,em,i,span")&&ed.schema.isValidChild("a",n.nodeName.toLowerCase())))if("IMG"===n.nodeName)disableOptions();else{var v=se.getContent({format:"text"});$("#text").val(v)}}else $("#text").val(se.getContent({format:"text"}));Wf.setDefaults(this.settings.defaults),$.each(["icon","size","date"],function(i,k){$("#layout_"+k+"_check").prop("checked",self.settings["option_"+k+"_check"]).change()})}"external"===ed.settings.filebrowser_position?Wf.createBrowsers($("#href"),function(files){var file=files.shift();self.selectFile(file)},"files"):$("#href").filebrowser().on("filebrowser:onfileclick",function(e,file,data){self.selectFile(file,data)}).on("filebrowser:onfileinsert",function(e,file,data){self.selectFile(file,data)}),Wf.updateStyles()},insert:function(){tinyMCEPopup.editor;return""==$("#href").val()?(Wf.Modal.alert(tinyMCEPopup.getLang("filemanager_dlg.no_src","Please select a file or enter a file URL")),!1):""===$("#text:enabled").val()&&"link"===$("#format").val()?(Wf.Modal.alert(tinyMCEPopup.getLang("filemanager_dlg.no_text","Text for the file link is required")),!1):void this.insertAndClose()},insertAndClose:function(){tinyMCEPopup.restoreSelection();var el,content,ed=tinyMCEPopup.editor,se=ed.selection,n=se.getNode(),args={},html=[];tinymce.isWebKit&&ed.getWin().focus(),content=se.getContent();var ext=Wf.String.getExt($("#href").val());ext=ext.toLowerCase();var options=[];$("#layout").hasClass("uk-sortable")&&(options=$("#layout").sortable("toArray"));var format=this.settings.icon_format,icon=format.replace("{$name}",this.settings.icon_map[ext],"i");icon=Wf.String.path(this.settings.icon_path,icon),"/"==icon.charAt(0)&&(icon=icon.substring(1));var data={icon:'<img class="wf_file_icon" src="'+icon+'" style="border:0px;vertical-align:middle;max-width:inherit;" alt="'+ext+'" />',date:'<span class="wf_file_date" style="margin-left:5px;">'+$("input:text","#layout_date").val()+"</span>",size:'<span class="wf_file_size" style="margin-left:5px;">'+$("input:text","#layout_size").val()+"</span>",text:'<span class="wf_file_text">'+$("#text").val()+"</span>"},attribs=["href","title","target","id","style","class","rel","rev","charset","hreflang","dir","lang","tabindex","accesskey","type"];tinymce.each(attribs,function(k){var v=$("#"+k+":enabled").val();"href"==k&&(v=Wf.String.encodeURI(v,!0)),"class"==k&&(v=$("#classes").val()||""),"target"==k&&"download"==v&&(args.download=Wf.String.basename($("#href").val()),args[k]="_blank"),args[k]=v}),$.each(options,function(i,v){$("input:checkbox","#"+v).is(":checked")&&html.push(data[v.replace("layout_","")])});var ow=$("#format_openwith").val(),format=$("#format").val();if(ow&&(args.href=openwith[ow][format]+encodeURIComponent(decodeURIComponent(Wf.URL.toAbsolute(args.href)))),ed.undoManager.add(),"embed"===format){args.src=args.href,delete args.href;var json=this.serializeParameters(args),w=$("#width").val(),h=$("#height").val(),data={src:"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7","data-mce-json":json,width:w,height:h,alt:args.title||""};$.each(attribs,function(i,k){ed.schema.isValid("img",k)&&(data[k]=args[k])}),n&&ed.dom.is(n,".mceItemIframe")?(ed.dom.setAttribs(n,data),ed.dom.addClass(n,"mceItemIframe")):(ed.execCommand("mceInsertContent",!1,'<img id="__mce_tmp" src="javascript:;" />',{skip_undo:1}),n=ed.dom.get("__mce_tmp"),ed.dom.setAttrib(n,"id",""),ed.dom.setAttribs(n,data),ed.dom.addClass(n,"mceItemIframe"))}else{if(se.isCollapsed())ed.execCommand("mceInsertContent",!1,'<a href="#" id="__mce_tmp">'+html.join("")+"</a>",{skip_undo:1}),el=ed.dom.get("__mce_tmp");else{if(el=ed.dom.getParent(se.getNode(),"A"))args.href||ed.dom.remove(el,!0),$("#text").prop("disabled")||(el.innerHTML=html.join(""));else{var styles;tinymce.isWebKit&&n&&"IMG"==n.nodeName&&(styles=n.style.cssText),ed.execCommand("mceInsertLink",!1,{href:"#",id:"__mce_tmp"},{skip_undo:1}),el=ed.dom.get("__mce_tmp"),ed.dom.setAttrib(el,"id",""),$("#text").prop("disabled")||(el.innerHTML=html.join("")),styles&&ed.dom.setAttrib(n,"style",styles)}ed.dom.addClass(ed.dom.select("span.wf_file_size",el),$("#size_class").val()),ed.dom.addClass(ed.dom.select("span.wf_file_date",el),$("#date_class").val())}ed.dom.setAttribs(el,args),ed.dom.addClass(el,"wf_file"),WFPopups.createPopup(el)}tinyMCEPopup.close()},serializeParameters:function(args){var data=(tinyMCEPopup.editor,{});tinymce.each(args,function(v,k){""!==v&&("src"==k&&(v=v.replace(/&amp;/gi,"&")),data[k]=v)});var o={iframe:data};return JSON.stringify(o)},_setupSortables:function(){this.sortlist||{};$("#layout > div").click(function(e){var el=e.target,p=this,items=$.fn.filebrowser.getselected();if(!el.disabled&&$(el).is(":checkbox:checked, .layout_option_reload")){if($(el).is(":checkbox")&&$(el).siblings("input:text").val())return;if($(p).is("#layout_size, #layout_date")&&items.length){$("#insert").prop("disabled",!0),$(p).addClass("loading");var type=$(p).data("type");Wf.JSON.request("getFileDetails",$(items[0]).attr("id"),function(o){o.error||$("input:text",p).val(o[type]),$("#insert").prop("disabled",!1),$(p).removeClass("loading")})}}}),$("#layout").sortable({axis:"x"})},selectFile:function(file,data){var name=data.title,src=data.url;$("#href").val(src),$("input:text","#layout_size").val(Wf.String.formatSize($(file).data("size"))),$("input:text","#layout_date").val(Wf.String.formatDate($(file).data("modified"),this.settings.date_format)),1==this.settings.replace_text&&(""!==$("#text").val()&&1==this.settings.text_alert?Wf.Modal.confirm(tinyMCEPopup.getLang("filemanager_dlg.replace_text","Replace file link text with file name?"),function(state){state&&$("#text").val(name)}):$("#text").val(name))},setClasses:function(v){Wf.setClasses(v)}};window.FileManager=FileManager,$(document).ready(function(){FileManager.init()})}(jQuery);