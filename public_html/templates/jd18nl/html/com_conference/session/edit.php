<?php
/**
 * @package     Conference
 *
 * @author      Stichting Sympathy <info@stichtingsympathy.nl>
 * @copyright   Copyright (C) 2013 - [year] Stichting Sympathy. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://stichtingsympathy.nl
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');

$returnUrl = base64_encode(Uri::root() . Route::_('index.php?option=com_conference&view=profile', false));
$params    = JComponentHelper::getParams('com_conference');

JFactory::getDocument()->addScriptDeclaration("
        Joomla.submitbutton = function(task)
        {
            var form = document.getElementById('sessionForm');
            
            if (task == 'session.cancel' || document.formvalidator.isValid(form))
            {
                    " . $this->form->getField('description')->save() . "
                    Joomla.submitform(task, form);
            }
        }
");
?>
<div class="conference">
	<form name="sessionForm" id="sessionForm" class="form form-horizontal form-validate" action="<?php echo Route::_('index.php?option=com_conference&task=session.edit&id=' . $this->form->getValue('conference_session_id')); ?>" method="post" enctype="multipart/form-data">
		<?php
		echo  LayoutHelper::render('toolbar', array(
			'title' => 'COM_CONFERENCE_FIELD_SESSION',
			'view'  => 'session'
		));
		?>
		<div class="well well-small">
			<!-- Start row -->
			<div class="row-fluid">
				<!-- Start left -->
				<div class="span12">
					<?php echo $this->form->renderField('title'); ?>
					<div class="control-group">
						<label for="conference_level_id" class="control-label">
							<?php echo $this->form->getLabel('conference_level_id'); ?>
						</label>
						<div class="controls">
							<?php echo $this->form->getInput('conference_level_id'); ?>
							<span class="help-block"><a href="<?php echo JRoute::_('index.php?option=com_conference&view=levels')?>"><?php echo JText::_('COM_CONFERENCE_FIELD_LEVEL_DESC'); ?></a></span>
							
						</div>
					</div>
					<?php if ($params->get('language', 0)): ?>
						<?php echo $this->form->renderField('language'); ?>
					<?php endif;?>
					<hr>
					<?php echo $this->form->renderField('description'); ?>
					<hr>
					<div class="control-group">
						<label for="slides" class="control-label">
							<?php echo $this->form->getLabel('slides'); ?>
						</label>
						<div class="controls">
							<?php echo $this->form->getInput('slides'); ?>
							<span class="help-block"><?php echo JText::_('COM_CONFERENCE_FIELD_SLIDES_DESC')?></span>
						</div>
					</div>
				</div>
				<!-- End left -->
			</div>
		</div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo $returnUrl; ?>" />
		<?php echo $this->form->renderField('conference_session_id'); ?>
		<?php echo $this->form->renderField('enabled'); ?>
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>