techjoomla.jQuery(document).ready(function(){
	techjoomla.jQuery(".allUserAvaiable").click(function() {
			var li_id = techjoomla.jQuery(this).attr('id');
			var isChecked = techjoomla.jQuery('#'+li_id+' .contacts_check').is(":checked");

			if (isChecked == false)
			{
				techjoomla.jQuery('#'+li_id+' .thumbnail').css('border','1px solid #333');
				techjoomla.jQuery('#'+li_id+' .thumbnail').css('box-shadow','2px 2px 3px #333');
				techjoomla.jQuery('#'+li_id+' .contacts_check').prop('checked', true);
			}
			else
			{
				techjoomla.jQuery('#'+li_id+' .thumbnail').css('border','1px solid #ddd');
				techjoomla.jQuery('#'+li_id+' .thumbnail').css('box-shadow','none');
				techjoomla.jQuery('#'+li_id+' .contacts_check').prop('checked', false);
			}
		});
});

/**
 *
 * Recommend & Assign
 *
 */
function recommendation(tsk)
{
	var task_type = techjoomla.jQuery('#task_type').val();
	var task_sub_type = techjoomla.jQuery('#task_sub_type').val();

	if (task_type == "assign")
	{
		var sdField = (tsk == 'assignRecommendGroups') ? '#group_start_date' : '#start_date';
		var ddField = (tsk == 'assignRecommendGroups') ? '#group_due_date' : '#due_date';
		var daterangefrom = techjoomla.jQuery(sdField).val();
		var daterangeto = techjoomla.jQuery(ddField).val();

		if (daterangefrom ==  "" || daterangeto ==  "")
		{
			alert(Joomla.JText._("COM_JLIKE_SELECT_FILL_DATES"));

			if (daterangefrom == "")
			{
				techjoomla.jQuery(sdField).focus();
			}
			else if(daterangeto == "")
			{
				techjoomla.jQuery(ddField).focus();
			}

			return false;
		}

		var res = checkDateFormat(daterangefrom);

		if (res == false)
		{
			alert(Joomla.JText._("COM_JLIKE_INVALID_DATE_FORMAT") + daterangefrom);
			techjoomla.jQuery(sdField).val("");
			return false;
		}

		var res = checkDateFormat(daterangeto);

		if (res == false)
		{
			alert(Joomla.JText._("COM_JLIKE_INVALID_DATE_FORMAT") + daterangeto);
			techjoomla.jQuery(sdField).val("");
			return false;
		}

		if ((daterangefrom) > (daterangeto))
		{
			alert(Joomla.JText._("COM_JLIKE_START_GT_THAN_DUE_DATE"));
			return false;
		}

		// Check for only end date
		if (techjoomla.jQuery(ddField).val() != '')
		{
			var selectedDate = techjoomla.jQuery(ddField).val();
			var today = new Date();
			today.setHours(0, 0, 0, 0);
			assignEndDate = new Date(selectedDate);
			assignEndDate.setHours(0, 0, 0, 0);

			if(assignEndDate < today)
			{
				alert(Joomla.JText._("COM_JLIKE_START_GT_THAN_TODAY"));
				return false;
			}
		}
	}

	if(task_sub_type == 'self')
	{
		Joomla.submitform('recommend.assignRecommendUsers');
	}
	else
	{
		if (tsk == 'assignRecommendGroups')
		{
			if(techjoomla.jQuery('.recommend-popup-div input.user_groups:checked').length)
			{
				Joomla.submitform('recommend.assignRecommendGroups');
			}
			else
			{
				alert(Joomla.JText._("COM_JLIKE_SELECT_GROUP_TO_ASSIGN"));
				return false;
			}
		}
		else
		{
			if(techjoomla.jQuery('#jlike-users-list input[type=checkbox]:checked, #usersList input[type=checkbox]:checked').length)
			{
				Joomla.submitform('recommend.assignRecommendUsers');
			}
			else
			{
				alert(Joomla.JText._("COM_JLIKE_SELECT_USER_TO_RECOMMEND"));
				return false;
			}
		}
	}
}

function closePopUp()
{
	window.parent.SqueezeBox.close();
}


/**
* Date format checker
*/
function checkDateFormat(datevalue)
{
	// regular expression to match required date format
	regExp = /^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/;

	if (datevalue != '' && !datevalue.match(regExp))
	{
		return false;
	}

	return true;
}

jQuery(document).ready(function(){
	jQuery('.recomment-popup-form').on('shown.bs.tab', '#myTabTabs.nav-tabs li a', function(e) {
		var href = jQuery(this).attr('href');
			href = href.replace("#",'');

		if (href == 'groups')
		{
			jQuery('body').addClass('jlike_enrolGroups').removeClass('jlike_enrolUsers');
		}
		else
		{
			jQuery('body').addClass('jlike_enrolUsers').removeClass('jlike_enrolGroups');
		}
	});
});

