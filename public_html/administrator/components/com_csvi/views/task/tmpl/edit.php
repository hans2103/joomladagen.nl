<?php
/**
 * @package     CSVI
 * @subpackage  Tasks
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen');
?>
<form action="<?php echo JRoute::_('index.php?option=com_csvi&view=task&id=' . $this->item->csvi_task_id); ?>" method="post" name="adminForm"  id="adminForm" class="form-horizontal form-validate">
	<div class="row-fluid">
		<?php echo $this->form->renderFieldset('task'); ?>
	</div>
	<input type="hidden" name="csvi_task_id" value="<?php echo $this->item->csvi_task_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="1" />
	<?php echo JHtml::_('form.token'); ?>
</form>
