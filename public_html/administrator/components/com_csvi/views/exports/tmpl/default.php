<?php
/**
 * @package     CSVI
 * @subpackage  Export
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

// Add chosen
JHtml::_('formbehavior.chosen');

// Check if database type is set to MySQLi
$jconfig = JFactory::getConfig();

if ($jconfig->get('dbtype') !== 'mysqli')
{
	?><div class="span12"><div class="error"><?php
		echo JText::_('COM_CSVI_CONFIGURE_MYSQLI_EXPORT');
	?></div></div><?php
}
else
{
	?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php if (count($this->templates) > 0) : ?>
			<form action='<?php echo JRoute::_('index.php?option=com_csvi&view=export');?>'
				name='adminForm' id='adminForm' method='post' class='form-horizontal'>
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="return" value="">
				<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken();?>" value="1" />
				<?php
					$select = JHtml::_('select.option', '', JText::_('COM_CSVI_MAKE_A_CHOICE'), 'csvi_template_id', 'template_name');
					array_unshift($this->templates, $select);
					echo JHtml::_(
						'select.genericlist',
						$this->templates,
						'csvi_template_id',
						'class="input-xxlarge advancedSelect"',
						'csvi_template_id',
						'template_name',
						$this->lastRunId
					);
				?>
			</form>
		<?php else : ?>
			<?php echo JText::_('COM_CSVI_NO_TEMPLATES_CREATE_THEM'); ?>
		<?php endif; ?>
	</div>
<?php
}
