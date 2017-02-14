/* jce - 2.6.8 | 2017-02-01 | http://www.joomlacontenteditor.net | Copyright (C) 2006 - 2017 Ryan Demmer. All rights reserved | GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html */
!function(){var each=tinymce.each;tinymce.create("tinymce.plugins.CaptionPlugin",{init:function(ed,url){function isCaption(n){return n&&ed.dom.getParent(n,".mceItemCaption")}ed.onInit.add(function(){ed.settings.compress.css||ed.dom.loadCSS(url+"/css/content.css")}),ed.onSetContent.add(function(ed){var dom=ed.dom;each(dom.select(".jce_caption, .wf_caption",ed.getBody()),function(n){dom.addClass(n,"mceItemCaption")})}),ed.onPreProcess.add(function(ed,o){var dom=ed.dom;o.set&&each(dom.select(".jce_caption, .wf_caption",o.node),function(n){dom.addClass(n,"mceItemCaption")}),o.get&&each(dom.select(".mceCaption",o.node),function(n){dom.removeClass(n,"mceItemCaption")}),each(dom.select(".jce_caption, .wf_caption",o.node),function(n){var w=0;dom.getStyle(n,"max-width");each(n.childNodes,function(c){if("IMG"===c.nodeName){w=c.getAttribute("width"),ed.getParam("caption_responsive",1)&&ed.dom.setStyle(c,"width","100%");var img=new Image;img.onload=function(){var iw=img.width;img.height;dom.setStyle(n,"max-width",(w||iw)+"px"),dom.setAttrib(c,"width",w||iw)},img.src=c.src}"SPAN"===c.nodeName&&ed.dom.getStyle(c,"max-width")&&ed.dom.setStyle(c,"max-width",null)}),dom.setStyle(n,"display","block"),ed.getParam("caption_responsive",1)&&ed.dom.setStyle(n,"width","100%"),dom.setAttrib(n,"data-mce-style",n.style.cssText)})}),ed.addCommand("mceCaption",function(){var se=ed.selection,n=se.getNode(),p=ed.dom.getParent(n,".mceItemCaption");return("SPAN"===n.nodeName&&p||n===p)&&(n=ed.dom.select("img",p)[0]),"IMG"===n.nodeName&&(ed.dom.select(n),void ed.windowManager.open({file:ed.getParam("site_url")+"index.php?option=com_jce&view=editor&plugin=caption",width:600+ed.getLang("caption.delta_width",0),height:500+ed.getLang("caption.delta_height",0),inline:1,popup_css:!1},{plugin_url:url}))}),ed.addCommand("mceCaptionDelete",function(){var c,f,a,se=ed.selection,n=se.getNode();c=ed.dom.getParent(n,".mceItemCaption"),c&&(tinymce.each(ed.dom.select("img",c),function(o){var styles={};tinymce.each(["Top","Right","Bottom","Left"],function(s){styles["margin"+s]=ed.dom.getStyle(c,"margin"+s)}),f=ed.dom.getStyle(c,"float"),"left"!==f&&"right"!==f||(styles.float=f),a=ed.dom.getStyle(c,"vertical-align"),"top"!==a&&"middle"!==a&&"bottom"!==a||(styles.verticalAlign=a),styles.marginLeft&&"auto"===styles.marginLeft&&styles.marginRight&&"auto"===styles.marginRight&&(styles.display="block"),styles.width="",ed.dom.setStyles(o,styles),ed.dom.setAttrib(o,"data-mce-style",o.style.cssText)}),ed.dom.remove(ed.dom.select("span, div",c)),ed.dom.remove(c,!0))}),ed.addButton("caption_add",{title:"caption.desc",cmd:"mceCaption",image:url+"/img/caption_add.png"}),ed.addButton("caption_delete",{title:"caption.delete",cmd:"mceCaptionDelete",image:url+"/img/caption_delete.png"}),ed.onNodeChange.add(function(ed,cm,n,co){var s=isCaption(n);if(cm.setDisabled("formatselect",s),cm.setDisabled("blockquote",s),cm.setActive("caption_delete",s),cm.setActive("caption_add",s),cm.setDisabled("caption_add",!s),cm.setDisabled("caption_delete",!s),s||"IMG"!=n.nodeName||cm.setDisabled("caption_add",!1),s&&(tinymce.isIE&&document.documentMode>=9&&"IMG"==n.nodeName&&ed.selection.select(n),"IMG"===n.nodeName)){var p=ed.dom.getParent(n,".mceItemCaption");ed.dom.setStyle(p,"max-width",n.getAttribute("width")),ed.dom.setAttrib(p,"data-mce-style",p.style.cssText)}})}}),tinymce.PluginManager.add("caption",tinymce.plugins.CaptionPlugin)}();