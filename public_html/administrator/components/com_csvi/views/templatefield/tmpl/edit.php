<?php
/**
 * Template field editing page
 *
 * @author 		RolandD Cyber Produksi
 * @link 		https://csvimproved.com
 * @copyright 	Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version 	$Id: default.php 1924 2012-03-02 11:32:38Z RolandD $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

// Load some needed behaviors
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('formbehavior.chosen');

$class = 'span12';

if ($this->extraHelp)
{
	$class = 'span11';
}

?>
<h4><?php echo $this->template->getName(); ?></h4>
<form
	action="<?php echo JRoute::_('index.php?option=com_csvi&view=templatefield&id=' . $this->item->csvi_templatefield_id); ?>"
	method="post"
	name="adminForm"
	id="adminForm"
	class="form-validate form-horizontal">
	<div class="row-fluid">
		<div class="<?php echo $class; ?>">
			<?php echo $this->form; ?>
		</div>
		<?php
		if ($this->extraHelp)
		{
			$layout = new JLayoutFile('csvi.help-arrow');
			echo $layout->render((object) array(new stdClass));
		}
		?>
	</div>
	<div id="pluginfields"></div>
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="csvi_templatefield_id" value="<?php echo $this->item->csvi_templatefield_id; ?>" />
	<input type="hidden" name="csvi_template_id" value="<?php echo $this->item->csvi_template_id; ?>" />
	<input type="hidden" name="fromdatabase" value="<?php echo $this->item->fromdatabase; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<?php
	$layout = new JLayoutFile('csvi.modal');
	echo $layout->render(array('ok-btn-dismiss' => true));
?>

<script type="text/javascript">
	// Turn off the help texts
	jQuery('.help-block').toggle();

	Joomla.submitbutton = function(task)
	{
		if (task == 'hidetips') {
			jQuery('.help-block').toggle();
			return false;
		}
		else if (task == 'templatefield.cancel' || document.formvalidator.isValid(document.getElementById('adminForm'))) {
			Joomla.submitform(task);
		}
		else {
			showMsg(
				'<?php echo JText::_('COM_CSVI_ERROR'); ?>',
				'<?php echo JText::_('COM_CSVI_INCOMPLETE_FORM'); ?>',
				'<?php echo JText::_('COM_CSVI_CLOSE_DIALOG'); ?>'
			);

			return false;
		}
	}
</script>
