jQuery(function($){

  // Body Padding bottom
  $(window).load(function() {
    $('body').css('padding-bottom', $('.sp-pagebuilder-page-tools').outerHeight());
  });

  $(window).resize(function() {
    $('body').css('padding-bottom', $('.sp-pagebuilder-page-tools').outerHeight());
  });

  $('.sp-pagebuilder-core-options-toggler').on('click', function(event) {
    event.preventDefault();
    $('.sp-pagebuilder-core-options').toggleClass('active');
  })

  // Page options
  var arrval = {};
  $.fn.openPopupAlt = function() {
    $('#page-options').addClass('sp-pagebuilder-modal-overlay-after-open');
    $('#page-options').find('.sp-pagebuilder-modal-content').addClass('sp-pagebuilder-modal-content-after-open');
    $('body').addClass('sp-pagebuilder-modal-open');

    //Store
    $('.sp-pagebuilder-modal-alt .form-group').find('>input,>textarea,>select').each(function() {
      arrval[$(this).attr('id')] = $(this).val();
    });
  };

  $.fn.closePopupAlt = function(options) {
    var settings = $.extend({
      reset: false
    }, options );

    $('#page-options').addClass('sp-pagebuilder-modal-overlay-before-close');
    $('#page-options').find('.sp-pagebuilder-modal-content').addClass('sp-pagebuilder-modal-content-before-close');
    $('#page-options').removeClass('sp-pagebuilder-modal-overlay-before-close sp-pagebuilder-modal-overlay-after-open');
    $('#page-options').find('.sp-pagebuilder-modal-content').removeClass('sp-pagebuilder-modal-content-before-close sp-pagebuilder-modal-content-after-open');
    $('body').removeClass('sp-pagebuilder-modal-open');

    if(settings.reset) {
      $('.sp-pagebuilder-modal-alt .form-group').find('>input,>textarea,>select').each(function() {
        $(this).val(arrval[$(this).attr('id')]);

        if( ($(this).attr('id') == 'jform_og_image') && (arrval[$(this).attr('id')] !='' ) )	{
          $(this).prev('.sppb-media-preview').removeClass('no-image').attr('src', pagebuilder_base + arrval[$(this).attr('id')]);
        }
      });
    }

    return this;
  };

  $(document).on('click', '#btn-page-options', function(event) {
    event.preventDefault();
    $().openPopupAlt();
  });

  $(document).on('click', '.sp-pagebuilder-modal-alt .sp-pagebuilder-modal-content-after-open', function(event) {
    if (event.target !== this)
    return;

    $().closePopupAlt({
      reset: true
    });
  });

  $(document).on('click', '#btn-cancel-page-options', function(event) {
    event.preventDefault();
    $().closePopupAlt({
      reset: true
    });
  });

  $(document).on('click', '#btn-apply-page-options', function(event) {
    event.preventDefault();
    $().closePopupAlt();
    $('#sp-pagebuilder-css').text($('#jform_css').val());
  });

});
