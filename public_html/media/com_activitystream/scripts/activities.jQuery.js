(function( $ ) {
	if(typeof(techjoomla) == 'undefined')
	{
		var techjoomla = {};
	}

	if(typeof techjoomla.jQuery == "undefined")
	{
		techjoomla.jQuery = jQuery;
	}

	techjoomla.jQuery(document).ready(function(){
	getActivities();
});

getActivities = function(){
	var widgetNumber = 0;

	techjoomla.jQuery('[tj-activitystream-widget]').each(function(){
		widgetNumber++;
		techjoomla.jQuery(this).attr('id',"tj-activitystream" + widgetNumber);
		techjoomla.jQuery(this).attr('activityNumber',0);
		techjoomla.jQuery(this).attr('start',0);
		techjoomla.jQuery(this).html("<a id='load-more-activity-button"+techjoomla.jQuery(this).attr('id')+"'></a>");

		var activity = initActivities(this);
	});
}
})($);

function initActivities(ele)
{
	var type = techjoomla.jQuery(ele).attr("tj-activitystream-type");
	var actor_id = techjoomla.jQuery(ele).attr("tj-activitystream-actor-id");
	var object_id = techjoomla.jQuery(ele).attr("tj-activitystream-object-id");
	var target_id = techjoomla.jQuery(ele).attr("tj-activitystream-target-id");
	var from_date = techjoomla.jQuery(ele).attr("tj-activitystream-from-date");
	var view = techjoomla.jQuery(ele).attr("tj-activitystream-bs");
	var theme = techjoomla.jQuery(ele).attr("tj-activitystream-theme");
	var lang = techjoomla.jQuery(ele).attr("tj-activitystream-language");
	var limit = techjoomla.jQuery(ele).attr("tj-activitystream-limit");
	var activityNumber = techjoomla.jQuery(ele).attr("activityNumber");
	var start = techjoomla.jQuery(ele).attr("start");
	var url = root_url+"index.php?option=com_activitystream&task=activities.getActivities";

	if (typeof type != 'undefined')
	{
		url += "&type="+type;
	}
	if (typeof actor_id != 'undefined')
	{
		url += "&actor_id="+actor_id;
	}
	if (typeof object_id != 'undefined')
	{
		url += "&object_id="+object_id;
	}
	if (typeof target_id != 'undefined')
	{
		url += "&target_id="+target_id;
	}
	if (typeof from_date != 'undefined')
	{
		url += "&from_date="+from_date;
	}
	if (typeof limit != 'undefined')
	{
		url += "&limit="+limit;
	}

	techjoomla.jQuery.ajax({
		url: url+"&start="+start,
		type: 'GET',
		dataType: 'json',
		async:false,
		success: function(result)
		{
			if (result.success != false)
			{
				replaceTemplate(result.data.results, theme, view, lang, result.data.total, ele);
			}
		}
	});
}
function replaceTemplate(activitiesData,theme,view, lang, total, ele)
{
	var activityNumber = techjoomla.jQuery(ele).attr("activityNumber");
	var start = techjoomla.jQuery(ele).attr("start");

	activityData = activitiesData[activityNumber];
	var html = '';
	var templatePath = '';
	if (!activityData.template)
	{
		templatePath = root_url+"media/com_activitystream/themes/"+theme+"/templates/"+view+"/default.mustache";
	}
	else
	{
		templatePath = root_url+"media/com_activitystream/themes/"+theme+"/templates/"+view+"/"+activityData.template;
	}

	techjoomla.jQuery.ajax({
	method: 'GET',
	url: templatePath,
	async:false,
	success: function(res,stat,xhr)
		{
			if (!activityData.template)
			{
				var formatted_text = Mustache.render(activityData.formatted_text, {actor : activityData.actor, object: activityData.object, target: activityData.target});
				activityData.formatted_text = formatted_text;
				html = Mustache.render(res, activityData);
			}
			else
			{
				html = Mustache.render(res, activityData);
			}
		}
	}).done( function(){
		techjoomla.jQuery(ele).append(html);
		techjoomla.jQuery(ele).children(".language").not('.'+lang).remove();

		activityNumber++;
		start++;

		techjoomla.jQuery(ele).attr("activityNumber", activityNumber);
		techjoomla.jQuery(ele).attr("start", start);

		var elementId = techjoomla.jQuery(ele).attr('id');

		if (Number(start) < Number(total))
		{
			techjoomla.jQuery('#load-more-activity-button'+elementId).attr("onclick","loadMoreActivities('"+elementId+"')");
			techjoomla.jQuery('#load-more-activity-button'+elementId).attr("class",'btn btn-primary btn-md pull-right');
			techjoomla.jQuery('#load-more-activity-button'+elementId).html("Load More Activities");
			techjoomla.jQuery('#load-more-activity-button'+elementId).insertAfter(ele);
		}
		else
		{
			techjoomla.jQuery('#load-more-activity-button'+elementId).remove();
		}

		if(activityNumber < activitiesData.length)
		{
			replaceTemplate(activitiesData,theme,view, lang, total, ele);
		}
	}
	);
}

function loadMoreActivities(eleId)
{
	techjoomla.jQuery(techjoomla.jQuery("#"+eleId)).attr("activityNumber", 0);
	initActivities(techjoomla.jQuery("#"+eleId));
}
