jQuery(document).ready(function(){
	Joomla.submitbutton = function(task)
	{
		if (task == 'fields.delete')
		{
			if (confirm(Joomla.JText._('COM_TJFIELD_CONFIRM_DELETE_FIELD')) == false)
			{
				return false;
			}

			if (confirm(Joomla.JText._('COM_TJFIELD_CONFIRM_DELETE_REFRENCE_DATA')) == false)
			{
				return false;
			}
		}
		Joomla.submitform(task);
	}
});
