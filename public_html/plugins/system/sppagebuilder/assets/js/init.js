jQuery(document).ready(function($) {

	if(spPagebuilderEnabled) {
		$(spIntergationElement).hide();
		$('.sp-pagebuilder-admin').show();
		$('.btn-action-sppagebuilder').addClass('sp-pagebuilder-btn-success active');
	} else {
		$('.sp-pagebuilder-admin').hide();
		$(spIntergationElement).show();
		$('.btn-action-editor').addClass('sp-pagebuilder-btn-success active');
	}

	$('.sp-pagebuilder-btn-switcher').on('click',function(event){
		event.preventDefault();

		$('.sp-pagebuilder-btn-switcher').removeClass('active sp-pagebuilder-btn-success');
		$(this).addClass('active').addClass('sp-pagebuilder-btn-success');

		var action = $(this).data('action');
		if (action === 'editor') {
			$('.sp-pagebuilder-admin').hide();
			$(spIntergationElement).show();
			$('#jform_attribs_sppagebuilder_active').val('0');
		} else {
			$(spIntergationElement).hide();
			$('.sp-pagebuilder-admin').show();
			$('#jform_attribs_sppagebuilder_active').val('1');
		}

	});

});
