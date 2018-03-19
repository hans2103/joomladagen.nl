jQuery(document).ready(function(){
	// Hide all the fields
	jQuery('.replacefilter').children('div').hide();

	// Show only the selected fields
	jQuery('.operationTrigger option:selected').each(function(){
		// Get the classname
		var className = jQuery(this).attr('class');

		// Get the working area
		var fields = jQuery(this).parents('.operation').next();

		if (className)
		{
			showFields(fields, className);
		}
	});

	 // Show the field options for the chosen type
	jQuery('table').on('change', '.operationTrigger', function () {
		// Get the working area
		var fields = jQuery(this).parents('.operation').next();

		// Hide all the existing filters
		fields.children('div').hide();

		// Get the class to toggle on
		var className = jQuery(this).parent().find('option:selected').attr('class');

		if (className)
		{
			showFields(fields, className);
		}
	});

	 // Hide all the field options when a new row is added
	jQuery(document).on('subform-row-add', function(event, row) {
		var fields = jQuery(row).find('td.replacefilter');

		// Hide all the existing filters
		fields.children('div').hide();

		// Get the class to toggle on
		var className = jQuery(this).parent().find('option:selected').attr('class');

		if (className)
		{
			showFields(fields, className);
		}
	})
});

function showFields(fields, className)
{
	// Enable the required filters
	jQuery(fields).find('.' + className).show().children().show();
}