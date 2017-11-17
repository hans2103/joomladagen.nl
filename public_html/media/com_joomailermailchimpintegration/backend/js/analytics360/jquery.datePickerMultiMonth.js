/**
 * Copyright (c) 2008 Kelvin Luck (http://www.kelvinluck.com/)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) 
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 **/
(function($j){$j.fn.extend({datePickerMultiMonth:function(s)
{s.numMonths=s.numMonths||2;$j(this).each(function()
{var dps=$j.extend({},$j.fn.datePicker.defaults,s);var $jdpmm=$j(this);var pickers=[];var basePicker;var m;if(s.inline){$jdpmm.html('');for(var i=0;i<s.numMonths;i++)
{(function(i){var first=i==0;var last=i==s.numMonths-1;var $jdate=$j('<div></div>').datePicker(dps).bind('dpMonthChanged',function(event,displayedMonth,displayedYear)
{if(first){$jdpmm.trigger('dpMonthChanged',[displayedMonth,displayedYear]);}else{pickers[i-1].dpSetDisplayedMonth(displayedMonth-1,displayedYear);}
if(!last){pickers[i+1].dpSetDisplayedMonth(displayedMonth+1,displayedYear);}
return false;}).bind('dateSelected',function(event,date,$jtd,status)
{if(first){$jdpmm.trigger('dateSelected',[date,$jtd,status]);}else{pickers[i-1].dpSetSelected(date.asString(),status,false);}
if(!last){pickers[i+1].dpSetSelected(date.asString(),status,false);}
return false;}).bind('dateRangeSelected',function(event,start,end){if(first){$jdpmm.trigger('dateRangeSelected',[start,end]);}else{pickers[i-1].dpSetSelectedRange(start.asString(),end.asString(),false);}
if(!last){pickers[i+1].dpSetSelectedRange(start.asString(),end.asString(),false);}
return false;});$jdate.find('.dp-nav-prev').css('display',first?'block':'none');$jdate.find('.dp-nav-next').css('display',last?'block':'none');pickers.push($jdate);$jdpmm.append($jdate);})(i);}
basePicker=pickers[0];}else{var displayedMonth;var displayedYear;var selectedDate;if(dps.closeOnSelect==false)throw new Error("Popup multi month date pickers must close on select");if(dps.selectMultiple==true)throw new Error("Popup multi month date pickers aren't compatible with selectMultiple");$jdpmm.datePicker(dps).bind('dateSelected',function(event,date,$jtd,status)
{selectedDate=date.asString();}).bind('dpDisplayed',function(event,datePickerDiv)
{var $jpopup=$j(datePickerDiv).empty().css({width:'auto'});var d=$jdpmm.dpGetSelected();if(d.length){selectedDate=new Date(d[0]).asString();}
for(var i=0;i<s.numMonths;i++){(function(i){var s=$j.extend({},dps);s.inline=true;s.month=displayedMonth+i;s.year=displayedYear;var last=i==s.numMonths-1;var first=i==0;var $jdp=$j('<div></div>');$jpopup.append($jdp);$jdp.datePicker(s).bind('dpMonthChanged',function(event,newMonth,newYear)
{if(i==0){displayedMonth=newMonth;displayedYear=newYear;}
if(!first){pickers[i-1].dpSetDisplayedMonth(newMonth-1,newYear);}
if(!last){pickers[i+1].dpSetDisplayedMonth(newMonth+1,newYear);}
return false;}).bind('dateSelected',function(event,date,$jtd,status)
{var d=date.asString();if(d!=selectedDate){basePicker.dpSetSelected(date.asString());basePicker.dpClose();}}).find('.dp-nav-next').css('display',last?'block':'none').end().find('.dp-nav-prev').css('display',first?'block':'none').end();if(selectedDate){$jdp.dpSetSelected(selectedDate,true,false);}
pickers.push($jdp);})(i);}}).bind('dpMonthChanged',function(event,newMonth,newYear)
{displayedMonth=newMonth;displayedYear=newYear;}).bind('dpClosed',function(event,selected)
{pickers=[];});basePicker=$jdpmm;}
$jdpmm.data('dpBasePicker',basePicker);basePicker.dpSetDisplayedMonth(1,3000);basePicker.dpSetDisplayedMonth(Number(s.month),Number(s.year));});return this;},dpmmGetSelected:function()
{var basePicker=$j(this).data('dpBasePicker');return basePicker.dpGetSelected();},dpmmSetSelectedRange:function(start,end,moveToMonth,dispatchEvents){var basePicker=$j(this).data('dpBasePicker');return basePicker.dpSetSelectedRange(start,end,moveToMonth,dispatchEvents);},dpmmSetSelected:function(d,v,m,e){var basePicker=$j(this).data('dpBasePicker');return basePicker.dpSetSelected(d,v,m,e);},dpmmClearSelected:function(){var basePicker=$j(this).data('dpBasePicker');var c=_getController(basePicker[0]);if(c){return c.clearSelected();}
return null;}});function _getController(ele)
{if(ele._dpId)return $j.event._dpCache[ele._dpId];return false;};})(jQuery);