/**
 * Copyright (c) 2008 Kelvin Luck (http://www.kelvinluck.com/)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) 
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * $jId: jquery.datePicker.js 70 2009-04-05 19:25:15Z kelvin.luck $j
 **/
(function($j){$j.fn.extend({renderCalendar:function(s)
{var dc=function(a)
{return document.createElement(a);};s=$j.extend({},$j.fn.datePicker.defaults,s);if(s.showHeader!=$j.dpConst.SHOW_HEADER_NONE){var headRow=$j(dc('tr'));for(var i=Date.firstDayOfWeek;i<Date.firstDayOfWeek+7;i++){var weekday=i%7;var day=Date.dayNames[weekday];headRow.append(jQuery(dc('th')).attr({'scope':'col','abbr':day,'title':day,'class':(weekday==0||weekday==6?'weekend':'weekday')}).html(s.showHeader==$j.dpConst.SHOW_HEADER_SHORT?day.substr(0,1):day));}};var calendarTable=$j(dc('table')).attr({'cellspacing':2}).addClass('jCalendar').append((s.showHeader!=$j.dpConst.SHOW_HEADER_NONE?$j(dc('thead')).append(headRow):dc('thead')));var tbody=$j(dc('tbody'));var today=(new Date()).zeroTime();var month=s.month==undefined?today.getMonth():s.month;var year=s.year||today.getFullYear();var currentDate=new Date(year,month,1);var firstDayOffset=Date.firstDayOfWeek-currentDate.getDay()+1;if(firstDayOffset>1)firstDayOffset-=7;var weeksToDraw=Math.ceil(((-1*firstDayOffset+1)+currentDate.getDaysInMonth())/7);currentDate.addDays(firstDayOffset-1);var doHover=function(firstDayInBounds)
{return function()
{if(s.hoverClass){var $jthis=$j(this);if(!s.selectWeek){$jthis.addClass(s.hoverClass);}else if(firstDayInBounds&&!$jthis.is('.disabled')){$jthis.parent().addClass('activeWeekHover');}}}};var unHover=function()
{if(s.hoverClass){var $jthis=$j(this);$jthis.removeClass(s.hoverClass);$jthis.parent().removeClass('activeWeekHover');}};var w=0;while(w++<weeksToDraw){var r=jQuery(dc('tr'));var firstDayInBounds=s.dpController?currentDate>s.dpController.startDate:false;for(var i=0;i<7;i++){var thisMonth=currentDate.getMonth()==month;var d=$j(dc('td')).text(currentDate.getDate()+'').addClass((thisMonth?'current-month ':'other-month ')+
(currentDate.isWeekend()?'weekend ':'weekday ')+
(thisMonth&&currentDate.getTime()==today.getTime()?'today ':'')).data('datePickerDate',currentDate.asString()).hover(doHover(firstDayInBounds),unHover);r.append(d);if(s.renderCallback){s.renderCallback(d,currentDate,month,year);}
currentDate=new Date(currentDate.getFullYear(),currentDate.getMonth(),currentDate.getDate()+1);}
tbody.append(r);}
calendarTable.append(tbody);return this.each(function()
{$j(this).empty().append(calendarTable);});},datePicker:function(s)
{if(!$j.event._dpCache)$j.event._dpCache=[];s=$j.extend({},$j.fn.datePicker.defaults,s);return this.each(function()
{var $jthis=$j(this);var alreadyExists=true;if(!this._dpId){this._dpId=$j.event.guid++;$j.event._dpCache[this._dpId]=new DatePicker(this);alreadyExists=false;}
if(s.inline){s.createButton=false;s.displayClose=false;s.closeOnSelect=false;$jthis.empty();}
var controller=$j.event._dpCache[this._dpId];controller.init(s);if(!alreadyExists&&s.createButton){controller.button=$j('<a href="#" class="dp-choose-date" title="'+$j.dpText.TEXT_CHOOSE_DATE+'">'+$j.dpText.TEXT_CHOOSE_DATE+'</a>').bind('click',function()
{$jthis.dpDisplay(this);this.blur();return false;});$jthis.after(controller.button);}
if(!alreadyExists&&$jthis.is(':text')){$jthis.bind('dateSelected',function(e,selectedDate,$jtd)
{this.value=selectedDate.asString();}).bind('change',function()
{if(this.value==''){controller.clearSelected();}else{var d=Date.fromString(this.value);if(d){controller.setSelected(d,true,true);}}});if(s.clickInput){$jthis.bind('click',function()
{$jthis.trigger('change');$jthis.dpDisplay();});}
var d=Date.fromString(this.value);if(this.value!=''&&d){controller.setSelected(d,true,true);}}
$jthis.addClass('dp-applied');})},dpSetDisabled:function(s)
{return _w.call(this,'setDisabled',s);},dpSetStartDate:function(d)
{return _w.call(this,'setStartDate',d);},dpSetEndDate:function(d)
{return _w.call(this,'setEndDate',d);},dpGetSelected:function()
{var c=_getController(this[0]);if(c){return c.getSelected();}
return null;},dpSetSelected:function(d,v,m,e)
{if(v==undefined)v=true;if(m==undefined)m=true;if(e==undefined)e=true;return _w.call(this,'setSelected',Date.fromString(d),v,m,e);},dpSetSelectedRange:function(start,end,moveToMonth,dispatchEvents){if(moveToMonth==undefined)moveToMonth=true;if(dispatchEvents==undefined)dispatchEvents=true;return _w.call(this,'setSelectedRange',Date.fromString(start),Date.fromString(end),moveToMonth,dispatchEvents);},dpSetDisplayedMonth:function(m,y)
{return _w.call(this,'setDisplayedMonth',Number(m),Number(y),true);},dpDisplay:function(e)
{return _w.call(this,'display',e);},dpSetRenderCallback:function(a)
{return _w.call(this,'setRenderCallback',a);},dpSetPosition:function(v,h)
{return _w.call(this,'setPosition',v,h);},dpSetOffset:function(v,h)
{return _w.call(this,'setOffset',v,h);},dpClose:function()
{return _w.call(this,'_closeCalendar',false,this[0]);},_dpDestroy:function()
{}});var _w=function(f,a1,a2,a3,a4)
{return this.each(function()
{var c=_getController(this);if(c){c[f](a1,a2,a3,a4);}});};function DatePicker(ele)
{this.ele=ele;this.displayedMonth=null;this.displayedYear=null;this.startDate=null;this.endDate=null;this.showYearNavigation=null;this.closeOnSelect=null;this.displayClose=null;this.rememberViewedMonth=null;this.selectMultiple=null;this.numSelectable=null;this.numSelected=null;this.verticalPosition=null;this.horizontalPosition=null;this.verticalOffset=null;this.horizontalOffset=null;this.button=null;this.renderCallback=[];this.selectedDates={};this.inline=null;this.context='#dp-popup';this.settings={};};$j.extend(DatePicker.prototype,{init:function(s)
{this.setStartDate(s.startDate);this.setEndDate(s.endDate);this.setDisplayedMonth(Number(s.month),Number(s.year));this.setRenderCallback(s.renderCallback);this.showYearNavigation=s.showYearNavigation;this.closeOnSelect=s.closeOnSelect;this.displayClose=s.displayClose;this.rememberViewedMonth=s.rememberViewedMonth;this.selectMultiple=s.selectMultiple;this.numSelectable=s.selectMultiple?s.numSelectable:1;this.numSelected=0;this.verticalPosition=s.verticalPosition;this.horizontalPosition=s.horizontalPosition;this.hoverClass=s.hoverClass;this.setOffset(s.verticalOffset,s.horizontalOffset);this.inline=s.inline;this.settings=s;if(this.inline){this.context=this.ele;this.display();}},setStartDate:function(d)
{if(d){this.startDate=Date.fromString(d);}
if(!this.startDate){this.startDate=(new Date()).zeroTime();}
this.setDisplayedMonth(this.displayedMonth,this.displayedYear);},setEndDate:function(d)
{if(d){this.endDate=Date.fromString(d);}
if(!this.endDate){this.endDate=(new Date('12/31/2999'));}
if(this.endDate.getTime()<this.startDate.getTime()){this.endDate=this.startDate;}
this.setDisplayedMonth(this.displayedMonth,this.displayedYear);},setPosition:function(v,h)
{this.verticalPosition=v;this.horizontalPosition=h;},setOffset:function(v,h)
{this.verticalOffset=parseInt(v)||0;this.horizontalOffset=parseInt(h)||0;},setDisabled:function(s)
{$je=$j(this.ele);$je[s?'addClass':'removeClass']('dp-disabled');if(this.button){$jbut=$j(this.button);$jbut[s?'addClass':'removeClass']('dp-disabled');$jbut.attr('title',s?'':$j.dpText.TEXT_CHOOSE_DATE);}
if($je.is(':text')){$je.attr('disabled',s?'disabled':'');}},setDisplayedMonth:function(m,y,rerender)
{if(this.startDate==undefined||this.endDate==undefined){return;}
var s=new Date(this.startDate.getTime());s.setDate(1);var e=new Date(this.endDate.getTime());e.setDate(1);var t;if((!m&&!y)||(isNaN(m)&&isNaN(y))){t=new Date().zeroTime();t.setDate(1);}else if(isNaN(m)){t=new Date(y,this.displayedMonth,1);}else if(isNaN(y)){t=new Date(this.displayedYear,m,1);}else{t=new Date(y,m,1)}
if(t.getTime()<s.getTime()){t=s;}else if(t.getTime()>e.getTime()){t=e;}
var oldMonth=this.displayedMonth;var oldYear=this.displayedYear;this.displayedMonth=t.getMonth();this.displayedYear=t.getFullYear();if(rerender&&(this.displayedMonth!=oldMonth||this.displayedYear!=oldYear))
{this._rerenderCalendar();$j(this.ele).trigger('dpMonthChanged',[this.displayedMonth,this.displayedYear]);}},setSelectedRange:function(start,end,moveToMonth,dispatchEvents){if(this.selectMultiple==false){return;}
if(start.valueOf()>end.valueOf()){var temp=end;end=start;start=temp;}
var s=this.settings;if(moveToMonth&&(this.displayedMonth!=start.getMonth()||this.displayedYear!=start.getFullYear())){this.setDisplayedMonth(start.getMonth(),start.getFullYear(),true);}
this.clearSelected();this.numSelected=0;var startValue=start.valueOf();var endValue=end.valueOf();var d=new Date(startValue);while(d.valueOf()<=endValue){this.selectedDates[d.toString()]=true;this.numSelected++;d.addDays(1);}
var selectorString='td.'+(start.getMonth()==this.displayedMonth?'current-month':'other-month');var cellDate=null;$j(selectorString,this.context).each(function(){cellDate=new Date($j(this).data('datePickerDate'));if(cellDate.valueOf()>=startValue&&cellDate.valueOf()<=endValue){$j(this).addClass('selected');}});if(dispatchEvents){}},setSelected:function(d,v,moveToMonth,dispatchEvents)
{if(d<this.startDate||d>this.endDate){return;}
var s=this.settings;if(s.selectWeek)
{d=d.addDays(-(d.getDay()-Date.firstDayOfWeek+7)%7);if(d<this.startDate)
{return;}}
if(v==this.isSelected(d))
{return;}
if(this.selectMultiple==false){this.clearSelected();}else if(v&&this.numSelected==this.numSelectable){return;}
if(moveToMonth&&(this.displayedMonth!=d.getMonth()||this.displayedYear!=d.getFullYear())){this.setDisplayedMonth(d.getMonth(),d.getFullYear(),true);}
this.selectedDates[d.toString()]=v;this.numSelected+=v?1:-1;var selectorString='td.'+(d.getMonth()==this.displayedMonth?'current-month':'other-month');var $jtd;$j(selectorString,this.context).each(function()
{if($j(this).data('datePickerDate')==d.asString()){$jtd=$j(this);if(s.selectWeek)
{$jtd.parent()[v?'addClass':'removeClass']('selectedWeek');}
$jtd[v?'addClass':'removeClass']('selected');}});$j('td',this.context).not('.selected')[this.selectMultiple&&this.numSelected==this.numSelectable?'addClass':'removeClass']('unselectable');if(dispatchEvents)
{var s=this.isSelected(d);$je=$j(this.ele);var dClone=Date.fromString(d.asString());$je.trigger('dateSelected',[dClone,$jtd,s]);$je.trigger('change');}},isSelected:function(d)
{return this.selectedDates[d.toString()];},getSelected:function()
{var r=[];for(s in this.selectedDates){if(this.selectedDates[s]==true){r.push(Date.parse(s));}}
return r;},clearSelected:function(dispatchEvents)
{for(var s in this.selectedDates){if(dispatchEvents==undefined||dispatchEvents)
{$je=$j(this.ele);var dClone=new Date(s.valueOf());$je.trigger('dateSelected',[dClone,$j(this),false]);$je.trigger('change');}}
this.selectedDates={};this.numSelected=0;$j('td.selected',this.context).removeClass('selected').parent().removeClass('selectedWeek');},display:function(eleAlignTo)
{if($j(this.ele).is('.dp-disabled'))return;eleAlignTo=eleAlignTo||this.ele;var c=this;var $jele=$j(eleAlignTo);var eleOffset=$jele.offset();var $jcreateIn;var attrs;var attrsCalendarHolder;var cssRules;if(c.inline){$jcreateIn=$j(this.ele);attrs={'id':'calendar-'+this.ele._dpId,'class':'dp-popup dp-popup-inline'};$j('.dp-popup',$jcreateIn).remove();cssRules={};}else{$jcreateIn=$j('body');attrs={'id':'dp-popup','class':'dp-popup'};cssRules={'top':eleOffset.top+c.verticalOffset,'left':eleOffset.left+c.horizontalOffset};var _checkMouse=function(e)
{var el=e.target;var cal=$j('#dp-popup')[0];while(true){if(el==cal){return true;}else if(el==document){c._closeCalendar();return false;}else{el=$j(el).parent()[0];}}};this._checkMouse=_checkMouse;c._closeCalendar(true);$j(document).bind('keydown.datepicker',function(event)
{if(event.keyCode==27){c._closeCalendar();}});}
if(!c.rememberViewedMonth)
{var selectedDate=this.getSelected()[0];if(selectedDate){selectedDate=new Date(selectedDate);this.setDisplayedMonth(selectedDate.getMonth(),selectedDate.getFullYear(),false);}}
$jcreateIn.append($j('<div></div>').attr(attrs).css(cssRules).append($j('<h2></h2>'),$j('<div class="dp-nav-prev"></div>').append($j('<a class="dp-nav-prev-year" href="#" title="'+$j.dpText.TEXT_PREV_YEAR+'">&lt;&lt;</a>').bind('click',function()
{return c._displayNewMonth.call(c,this,0,-1);}),$j('<a class="dp-nav-prev-month" href="#" title="'+$j.dpText.TEXT_PREV_MONTH+'">&lt;</a>').bind('click',function()
{return c._displayNewMonth.call(c,this,-1,0);})),$j('<div class="dp-nav-next"></div>').append($j('<a class="dp-nav-next-year" href="#" title="'+$j.dpText.TEXT_NEXT_YEAR+'">&gt;&gt;</a>').bind('click',function()
{return c._displayNewMonth.call(c,this,0,1);}),$j('<a class="dp-nav-next-month" href="#" title="'+$j.dpText.TEXT_NEXT_MONTH+'">&gt;</a>').bind('click',function()
{return c._displayNewMonth.call(c,this,1,0);})),$j('<div class="dp-calendar"></div>')).bgIframe());var $jpop=this.inline?$j('.dp-popup',this.context):$j('#dp-popup');if(this.showYearNavigation==false){$j('.dp-nav-prev-year, .dp-nav-next-year',c.context).css('display','none');}
if(this.displayClose){$jpop.append($j('<a href="#" id="dp-close">'+$j.dpText.TEXT_CLOSE+'</a>').bind('click',function()
{c._closeCalendar();return false;}));}
c._renderCalendar();$j(this.ele).trigger('dpDisplayed',$jpop);if(!c.inline){if(this.verticalPosition==$j.dpConst.POS_BOTTOM){$jpop.css('top',eleOffset.top+$jele.height()-$jpop.height()+c.verticalOffset);}
if(this.horizontalPosition==$j.dpConst.POS_RIGHT){$jpop.css('left',eleOffset.left+$jele.width()-$jpop.width()+c.horizontalOffset);}
$j(document).bind('mousedown.datepicker',this._checkMouse);}},setRenderCallback:function(a)
{if(a==null)return;if(a&&typeof(a)=='function'){a=[a];}
this.renderCallback=this.renderCallback.concat(a);},cellRender:function($jtd,thisDate,month,year){var c=this.dpController;var d=new Date(thisDate.getTime());$jtd.bind('click',function()
{var $jthis=$j(this);if(!$jthis.is('.disabled')){c.setSelected(d,!$jthis.is('.selected')||!c.selectMultiple,false,true);if(c.closeOnSelect){c._closeCalendar();}
if(!$j.browser.msie)
{$j(c.ele).trigger('focus',[$j.dpConst.DP_INTERNAL_FOCUS]);}}});if(c.isSelected(d)){$jtd.addClass('selected');if(c.settings.selectWeek)
{$jtd.parent().addClass('selectedWeek');}}else if(c.selectMultiple&&c.numSelected==c.numSelectable){$jtd.addClass('unselectable');}},_applyRenderCallbacks:function()
{var c=this;$j('td',this.context).each(function()
{for(var i=0;i<c.renderCallback.length;i++){$jtd=$j(this);c.renderCallback[i].apply(this,[$jtd,Date.fromString($jtd.data('datePickerDate')),c.displayedMonth,c.displayedYear]);}});return;},_displayNewMonth:function(ele,m,y)
{if(!$j(ele).is('.disabled')){this.setDisplayedMonth(this.displayedMonth+m,this.displayedYear+y,true);}
ele.blur();return false;},_rerenderCalendar:function()
{this._clearCalendar();this._renderCalendar();},_renderCalendar:function()
{$j('h2',this.context).html((new Date(this.displayedYear,this.displayedMonth,1)).asString($j.dpText.HEADER_FORMAT));$j('.dp-calendar',this.context).renderCalendar($j.extend({},this.settings,{month:this.displayedMonth,year:this.displayedYear,renderCallback:this.cellRender,dpController:this,hoverClass:this.hoverClass}));if(this.displayedYear==this.startDate.getFullYear()&&this.displayedMonth==this.startDate.getMonth()){$j('.dp-nav-prev-year',this.context).addClass('disabled');$j('.dp-nav-prev-month',this.context).addClass('disabled');$j('.dp-calendar td.other-month',this.context).each(function()
{var $jthis=$j(this);if(Number($jthis.text())>20){$jthis.addClass('disabled');}});var d=this.startDate.getDate();$j('.dp-calendar td.current-month',this.context).each(function()
{var $jthis=$j(this);if(Number($jthis.text())<d){$jthis.addClass('disabled');}});}else{$j('.dp-nav-prev-year',this.context).removeClass('disabled');$j('.dp-nav-prev-month',this.context).removeClass('disabled');var d=this.startDate.getDate();if(d>20){var st=this.startDate.getTime();var sd=new Date(st);sd.addMonths(1);if(this.displayedYear==sd.getFullYear()&&this.displayedMonth==sd.getMonth()){$j('.dp-calendar td.other-month',this.context).each(function()
{var $jthis=$j(this);if(Date.fromString($jthis.data('datePickerDate')).getTime()<st){$jthis.addClass('disabled');}});}}}
if(this.displayedYear==this.endDate.getFullYear()&&this.displayedMonth==this.endDate.getMonth()){$j('.dp-nav-next-year',this.context).addClass('disabled');$j('.dp-nav-next-month',this.context).addClass('disabled');$j('.dp-calendar td.other-month',this.context).each(function()
{var $jthis=$j(this);if(Number($jthis.text())<14){$jthis.addClass('disabled');}});var d=this.endDate.getDate();$j('.dp-calendar td.current-month',this.context).each(function()
{var $jthis=$j(this);if(Number($jthis.text())>d){$jthis.addClass('disabled');}});}else{$j('.dp-nav-next-year',this.context).removeClass('disabled');$j('.dp-nav-next-month',this.context).removeClass('disabled');var d=this.endDate.getDate();if(d<13){var ed=new Date(this.endDate.getTime());ed.addMonths(-1);if(this.displayedYear==ed.getFullYear()&&this.displayedMonth==ed.getMonth()){$j('.dp-calendar td.other-month',this.context).each(function()
{var $jthis=$j(this);if(Number($jthis.text())>d){$jthis.addClass('disabled');}});}}}
this._applyRenderCallbacks();},_closeCalendar:function(programatic,ele)
{if(!ele||ele==this.ele)
{$j(document).unbind('mousedown.datepicker');$j(document).unbind('keydown.datepicker');this._clearCalendar();$j('#dp-popup a').unbind();$j('#dp-popup').empty().remove();if(!programatic){$j(this.ele).trigger('dpClosed',[this.getSelected()]);}}},_clearCalendar:function()
{$j('.dp-calendar td',this.context).unbind();$j('.dp-calendar',this.context).empty();}});$j.dpConst={SHOW_HEADER_NONE:0,SHOW_HEADER_SHORT:1,SHOW_HEADER_LONG:2,POS_TOP:0,POS_BOTTOM:1,POS_LEFT:0,POS_RIGHT:1,DP_INTERNAL_FOCUS:'dpInternalFocusTrigger'};$j.dpText={TEXT_PREV_YEAR:'Previous year',TEXT_PREV_MONTH:'Previous month',TEXT_NEXT_YEAR:'Next year',TEXT_NEXT_MONTH:'Next month',TEXT_CLOSE:'Close',TEXT_CHOOSE_DATE:'Choose date',HEADER_FORMAT:'mmmm yyyy'};$j.dpVersion='$jId: jquery.datePicker.js 70 2009-04-05 19:25:15Z kelvin.luck $j';$j.fn.datePicker.defaults={month:undefined,year:undefined,showHeader:$j.dpConst.SHOW_HEADER_SHORT,startDate:undefined,endDate:undefined,inline:false,renderCallback:null,createButton:true,showYearNavigation:true,closeOnSelect:true,displayClose:false,selectMultiple:false,numSelectable:Number.MAX_VALUE,clickInput:false,rememberViewedMonth:true,selectWeek:false,verticalPosition:$j.dpConst.POS_TOP,horizontalPosition:$j.dpConst.POS_LEFT,verticalOffset:0,horizontalOffset:0,hoverClass:'dp-hover'};function _getController(ele)
{if(ele._dpId)return $j.event._dpCache[ele._dpId];return false;};if($j.fn.bgIframe==undefined){$j.fn.bgIframe=function(){return this;};};$j(window).bind('unload',function(){var els=$j.event._dpCache||[];for(var i in els){$j(els[i].ele)._dpDestroy();}});})(jQuery);