<?php
/**
 * @package    Pwtsitemap
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2018 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

// List order and direction
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));

// User instance for ACL
$user = JFactory::getUser();
?>

<form action="<?php echo JRoute::_('index.php?option=com_pwtsitemap&view=items'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php else : ?>
		<div id="j-main-container">
			<?php endif;?>
			<?php
			// Search tools bar
			echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
			?>
			<?php if (empty($this->items)) : ?>
				<div class="alert alert-no-items">
					<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php else : ?>
				<table class="table table-striped" id="articleList">
					<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', '', 'a.lft', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th width="1%" class="center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'COM_PWTSITEMAP_MENUTYPE', 'menutype_title', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JText::_("COM_PWTSITEMAP_FIELD_SHOW_IN_HTML"); ?>
						</th>
						<th>
							<?php echo JText::_("COM_PWTSITEMAP_FIELD_SHOW_IN_XML"); ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($this->items as $i => $item): ?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="order nowrap center hidden-phone">
								<?php
								$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
								?>
								<span class="sortable-handler<?php echo $iconClass ?>">
                                    <span class="icon-menu"></span>
                                </span>
								<input type="text" style="display:none" name="order[]" size="5" value="" />
							</td>
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td>
								<?php $prefix = JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
								<?php echo $prefix; ?>

								<?php if ($user->authorise('core.edit', 'com_menu')) : ?>
									<a href="index.php?option=com_menus&view=item&layout=edit&id=<?php echo $item->id; ?>">
										<?php echo $item->title; ?>
									</a>
								<?php else : ?>
									<?php echo $item->title; ?>
								<?php endif; ?>
								<span class="small break-word">
									<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
								</span>
								<div class="small">
									<?php echo JText::sprintf('COM_PWTSITEMAP_LANGUAGE', $this->escape($item->language)) ?>

									<?php echo JHtmlPwtSitemap::languageFlag($item->language); ?>
								</div>
							</td>
							<td>
								<?php echo $item->menutype_title; ?>
							</td>
							<td>
								<?php if ($item->params->get('addtohtmlsitemap', 0) !== 'disabled') : ?>
									<?php echo JHtmlPwtSitemap::radio('addtohtmlsitemap', $item->id, 'pwtsitemapradio', $item->params->get('addtohtmlsitemap', 1), JText::_('COM_PWTSITEMAP_FIELD_SHOW_IN_HTML')); ?>
								<?php endif; ?>
							</td>
							<td>
								<?php if ($item->params->get('addtoxmlsitemap', 0) !== 'disabled') : ?>
									<?php echo JHtmlPwtSitemap::radio('addtoxmlsitemap', $item->id, 'pwtsitemapradio', $item->params->get('addtoxmlsitemap', 1), JText::_('COM_PWTSITEMAP_FIELD_SHOW_IN_XML')); ?>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="6">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>
					<?php echo JHtml::_(
						'bootstrap.renderModal',
						'collapseModal',
						array(
							'title' => JText::_('COM_PWTSITEMAP_BATCH_OPTIONS'),
							'footer' => $this->loadTemplate('batch_footer')
						),
						$this->loadTemplate('batch_body')
					); ?>
				</table>
			<?php endif;?>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
</form>

<script type="text/javascript">
(function ($) {
	jQuery('.pwtsitemapradio').on('change', function() {
		var parameter     = $(this).attr('name').split('_')[0];
		var itemId        = $(this).attr('name').split('_')[1];
		var value         = $(this).val();
		var saveIndicator = $(this).closest('td').find('.save-indication').addClass('icon-ok');

		var request  = {
			'option': 'com_ajax',
			'plugin': 'pwtsitemap',
			'group': 'system',
			'itemId': itemId,
			'parameter': parameter,
			'value': value,
			'format':'json'
		};

		$(saveIndicator).removeClass('icon-ok').css({'background':'url(../media/system/images/modal/spinner.gif)', 'display':'inline-block', 'width':'16px', 'height':'16px'});

		$.ajax({
			type: 'POST',
			data: request,
			dataType: 'json',
			success: function (response) {
				$(saveIndicator).removeAttr('style').addClass('icon-ok');
			},
			error: function(xhr, status, err)
			{
				console.log(err)
			}
		});
	});
}(jQuery));
</script>
