/* jce - 2.8.10 | 2020-04-02 | https://www.joomlacontenteditor.net | Copyright (C) 2006 - 2020 Ryan Demmer. All rights reserved | GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html */
!function($){function uid(){var i,guid=(new Date).getTime().toString(32);for(i=0;i<5;i++)guid+=Math.floor(65535*Math.random()).toString(32);return"wf_"+guid+(counter++).toString(32)}function parseUrl(url){var data={},url=url.substring(url.indexOf("?")+1);return $.each(url.replace(/\+/g," ").split("&"),function(i,value){var val,param=value.split("="),key=decodeURIComponent(param[0]);2===param.length&&(val=decodeURIComponent(param[1]),"string"==typeof val&&val.length&&(data[key]=val))}),data}function upload(url,file){return new Promise(function(resolve,reject){var xhr=new XMLHttpRequest,formData=new FormData;xhr.upload&&(xhr.upload.onprogress=function(e){e.lengthComputable&&(file.loaded=Math.min(file.size,e.loaded))}),xhr.onreadystatechange=function(){4==xhr.readyState&&(200===xhr.status?resolve(xhr.responseText):reject(),file=formData=null)};var name=file.target_name||file.name;name=name.replace(/[\+\\\/\?\#%&<>"\'=\[\]\{\},;@\^\(\)£€$]/g,"");var args={method:"upload",id:uid(),inline:1,name:name},token=Joomla.getOptions?Joomla.getOptions("csrf.token"):"";token&&(args[token]=1),xhr.open("post",url,!0),xhr.setRequestHeader("X-Requested-With","XMLHttpRequest"),$.each(args,function(key,value){formData.append(key,value)}),formData.append("file",file),xhr.send(formData)})}function checkMimeType(file,filter){filter=filter.replace(/[^\w_,]/gi,"").toLowerCase();var map={images:"jpg,jpeg,png,gif,webp",media:"avi,wmv,wm,asf,asx,wmx,wvx,mov,qt,mpg,mpeg,m4a,m4v,swf,dcr,rm,ra,ram,divx,mp4,ogv,ogg,webm,flv,f4v,mp3,ogg,wav,xap",html:"html,htm,txt",files:"doc,docx,dot,dotx,ppt,pps,pptx,ppsx,xls,xlsx,gif,jpeg,jpg,png,webp,apng,pdf,zip,tar,gz,swf,rar,mov,mp4,m4a,flv,mkv,webm,ogg,ogv,qt,wmv,asx,asf,avi,wav,mp3,aiff,oga,odt,odg,odp,ods,odf,rtf,txt,csv,htm,html"},mimes=map[filter]||filter;return new RegExp(".("+mimes.split(",").join("|")+")$","i").test(file.name)}var counter=0,Joomla=window.Joomla||{};$.fn.WfMediaUpload=function(){return this.each(function(){function insertFile(value){var $wrapper=$(elm).parents(".field-media-wrapper"),inst=$wrapper.data("fieldMedia")||$wrapper.get(0);return inst&&inst.setValue?inst.setValue(value):$(elm).val(value).trigger("change"),!0}function getModalURL(){var url="",$wrapper=$(elm).parents(".field-media-wrapper"),inst=$wrapper.data("fieldMedia")||$wrapper.get(0);return inst&&(url=inst.options?inst.options.url||"":inst.url||$(inst).data("url")||""),url||$(elm).siblings("a.modal").attr("href")||""}function uploadAndInsert(url,file){if(!file.name)return!1;var params=parseUrl(url),url="index.php?option=com_jce",validParams=["task","context","plugin","filter"];return checkMimeType(file,params.filter||"images")?($.each(params,function(key,value){"task"===key&&(value="plugin.rpc"),$.inArray(key,validParams)===-1&&delete params[key]}),url+="&"+$.param(params),$(elm).prop("disabled",!0).addClass("wf-media-upload-busy"),void upload(url,file).then(function(response){$(elm).prop("disabled",!1).removeAttr("disabled").removeClass("wf-media-upload-busy");try{var o=JSON.parse(response),error="Unable to upload file";if($.isPlainObject(o)){o.error&&(error=o.error.message||error);var r=o.result;if(r){var files=r.files||[],item=files.length?files[0]:{};if(item.file)return insertFile(item.file)}}alert(error)}catch(e){alert("The server returned an invalid JSON response")}},function(){return $(elm).prop("disabled",!1).removeAttr("disabled").removeClass("wf-media-upload-busy"),!1})):(alert("The selected file is not supported."),!1)}var elm=document.getElementById(this.id)||this,url=getModalURL(elm);if(!url)return!1;var $uploadBtn=$('<a title="Upload" class="btn wf-media-upload-button" role="button" aria-label="Upload"><i role="presentation" class="icon-upload"></i><input type="file" aria-hidden="true" /></a>');$('input[type="file"]',$uploadBtn).on("change",function(e){if(e.preventDefault(),this.files){var file=this.files[0];file&&uploadAndInsert(url,file)}});var $selectBtn=$(elm).parent().find(".button-select");$uploadBtn.insertAfter($selectBtn),$(elm).on("drag dragstart dragend dragover dragenter dragleave drop",function(e){e.preventDefault(),e.stopPropagation()}).on("dragover dragenter",function(e){$(this).addClass("wf-media-upload-hover")}).on("dragleave",function(e){$(this).removeClass("wf-media-upload-hover")}).on("drop",function(e){var dataTransfer=e.originalEvent.dataTransfer;if(dataTransfer&&dataTransfer.files&&dataTransfer.files.length){var file=dataTransfer.files[0];file&&uploadAndInsert(url,file)}})})},$(document).ready(function($){$(".wf-media-input").removeAttr("readonly"),$(document).on("subform-row-add",function(event,row){$(row).find(".wf-media-input").removeAttr("readonly")}),$(".wf-media-input-upload").WfMediaUpload()})}(jQuery);