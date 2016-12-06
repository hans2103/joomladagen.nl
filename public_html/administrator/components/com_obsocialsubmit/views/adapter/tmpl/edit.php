<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined( '_JEXEC' ) or die;
global $isJ25;

JHtml::addIncludePath( JPATH_COMPONENT . '/helpers/html' );
JHtml::_( 'behavior.tooltip' );
JHtml::_( 'behavior.combobox' );
JHtml::_( 'behavior.formvalidation' );
if ( ! $isJ25 ) {
	JHtml::_( 'formbehavior.chosen', 'select' );
}

// Get Params Fieldsets
$this->fieldsets = $this->form->getFieldsets( 'params' );
$script = "Joomla.submitbutton = function(task)
	{
			if (task == 'adapter.cancel' || document.formvalidator.isValid(document.id('adapter-form'))) {
				Joomla.submitform(task, document.getElementById('adapter-form'));
				if (self != top) {
					window.top.setTimeout('window.parent.SqueezeBox.close()', 1000);
				}
			} else {
				alert('" . $this->escape( JText::_( 'JGLOBAL_VALIDATION_FORM_FAILED' ) ) . "');
			}
	};
	jQuery(document).ready(function(){
		jQuery('body').css({'position':'relative','height':'100%'}).append('<div class=\'overfloat\' style=\'background:rgba(0,0,0,0.4);z-index:99999;position:absolute;top:0;left:0;width:100%;height:100%;display:none\'></div>');
		jQuery('.obss_tag').bind('dragstart', function (ev) {
			var dataTransfer = ev.originalEvent.dataTransfer;
			dataTransfer.setData('Text',jQuery(this).text());
		});
		jQuery('.obss_tag').bind('dragleave', function (e) {
			jQuery('.overfloat').fadeIn();
			jQuery('.obss_tag_input .template').css({'z-index': '999999','position':'relative'});
		});
		jQuery('.obss_tag').on('dragend', function (e) {
			jQuery('.obss_tag_input .template').css({'z-index': '0'});
			jQuery('.overfloat').fadeOut('medium');
		});
	});

";
JFactory::getDocument()->addScriptDeclaration( $script );
?>
<form action="<?php echo JRoute::_( 'index.php?option=com_obsocialsubmit&view=adapter&layout=edit&id=' . (int) $this->item->id ); ?>" method="post" name="adminForm" id="adapter-form" class="form-validate form-horizontal">
	<div id="foobla">
		<div class="form-inline form-inline-header row-fluid">
			<div class="span7 form-horizontal"<?php echo $isJ25?' style="padding-top:6px;"':'';?>>
				<div class="control-group">
					<div class="input-prepend">
						<?php if ( $this->item->xml ) { ?>
							<?php if ( $text = trim( $this->item->xml->description ) ) {
								$dec_tooltip = "<p>" . JText::_( $text ) . "</p>";
							} else {
								$dec_tooltip = "<p class=\"alert alert-error\">" . JText::_( 'COM_OBSOCIALSUBMIT_ERR_XML' ) . "</p>";
							}
						} ?>
						<span class="add-on hasTooltip" data-original-title="<?php echo $dec_tooltip ?>" rel="tooltip"><?php echo $this->item->xml->name; ?></span>
						<span class="add-on" data-original-title="<?php echo $dec_tooltip ?>" rel="tooltip"><?php echo $this->item->xml->version; ?></span>
						<span class="add-on"><?php echo JText::_( 'COM_OBSOCIALSUBMIT_TITLE' ); ?></span>
						<?php echo $this->form->getInput( 'title' ); ?>
					</div>
				</div>
			</div>
			<div class="span5 form-horizontal">
				<div class="control-group">
					<?php echo $this->form->getInput( 'published' ); ?>
				</div>
			</div>
		</div>
		<div class="row-fluid">

			<?php echo $this->loadTemplate( 'options' ); ?>

			<div class="span4 form-horizontal">
				<div class="well well-small success">
					<!-- Connections -->
					<h4><?php echo JText::_( 'COM_OBSOCIALSUBMIT_CONNECTIONS_FIELDSET_LABEL' ); ?></h4>
					<?php echo $this->form->getInput( 'connections' ); ?>
				</div>
				<!--<div class="well well-small">
					<h4><?php echo JText::_( 'COM_OBSOCIALSUBMIT_FIELD_DESCRIPTION_LABEL' ); ?></h4>
					<?php echo $this->form->getInput( 'description' ); ?>
				</div>-->

				<input type="hidden" name="task" value="" />
				<?php echo JHtml::_( 'form.token' ); ?>
				<?php echo $this->form->getInput( 'addon' ); ?>
				<?php echo $this->form->getInput( 'addon_type' ); ?>
			</div>
		</div>
	</div>
</form>