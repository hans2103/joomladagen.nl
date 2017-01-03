<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined( '_JEXEC' ) or die;
global $isJ25;

JHTML::addIncludePath( JPATH_COMPONENT_ADMINISTRATOR . DS . 'helpers' . DS . 'html' );
if ( ! $isJ25 ) {
	JHtml::_( 'bootstrap.tooltip' );
	JHtml::_( 'behavior.multiselect' );
	JHtml::_( 'dropdown.init' );
	JHtml::_( 'formbehavior.chosen', 'select' );
}
$option = 'com_obsocialsubmit';
$ctr_url = 'index.php?option=' . $option . '&view=adapter';
JHTML::_( 'behavior.modal' );
$connections = $this->get( 'Connections' );
$user = JFactory::getUser();
$listOrder = $this->escape( $this->state->get( 'list.ordering' ) );
$listDirn = $this->escape( $this->state->get( 'list.direction' ) );
$canOrder = $user->authorise( 'core.edit.state', 'com_obsocialsubmit' );
$saveOrder = $listOrder == 'i.ordering';
$sortFields = $this->getSortFields();

?>
<div id="foobla">
	<div class="row-fluid">
		<div class="span8">
			<form action="<?php echo JRoute::_( 'index.php?option=com_obsocialsubmit&view=adapters' ); ?>" method="post" name="adminForm" id="adminForm">
				<div id="j-main-container">
					<div id="filter-bar" class="btn-toolbar">
						<div class="filter-select fltrt pull-left">
							<div class="btn-wrapper">
								<select name="filter_adapter" class="inputbox" onchange="this.form.submit()">
									<option value=""><?php echo JText::_( 'COM_OBSOCIALSUBMIT_ADAPTER_TYPE' ); ?></option>
									<?php
									echo JHtml::_( 'select.options', $this->get( 'AdapterTypes' ), 'element', 'element', $this->state->get( 'filter.adapter' ) );?>
								</select>
							</div>
							<div class="btn-wrapper">
								<select name="filter_state" class="inputbox" onchange="this.form.submit()">
									<option value=""><?php echo JText::_( 'JOPTION_SELECT_PUBLISHED' ); ?></option>
									<?php echo JHtml::_( 'select.options', obSocialSubmitHelper::getStateOptions(), 'value', 'text', $this->state->get( 'filter.state' ) ); ?>
								</select>
							</div>
						</div>
						<div class="btn-group pull-right hidden-phone">
							<div class="btn-wrapper">
								<label for="limit" class="element-invisible"><?php echo JText::_( 'JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC' ); ?></label>
								<?php echo $this->pagination->getLimitBox(); ?>
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
					<table class="table table-striped adminlist" id="adapterList">
						<thead>
						<tr>
							<th width="1%" class="hidden-phone">
								<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_( 'JGLOBAL_CHECK_ALL' ); ?>" onclick="Joomla.checkAll(this)" />
							</th>
							<th width="1%" class="nowrap center">
								<?php echo JHtml::_( 'grid.sort', 'JSTATUS', 'i.published', $listDirn, $listOrder ); ?>
							</th>
							<th>
								<?php echo JHtml::_( 'grid.sort', 'COM_OBSOCIALSUBMIT_FROM_EXTENSION_LABEL', 'i.title', $listDirn, $listOrder ); ?>
							</th>
							<th class="nowrap" width="1%">&nbsp;</th>
							<th width="40%" class="nowrap hidden-phone">
								<?php echo JText::_( 'COM_OBSOCIALSUBMIT_TO_SOCIAL_LABEL' ); ?>
							</th>
						</tr>
						</thead>
						<tfoot>
						<tr>
							<td colspan="6">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
						</tfoot>
						<tbody>
						<?php foreach ( $this->items as $i => $item ) :
							$link_duplicate = $ctr_url . '&layout=edit&id=' . $item->id . '';
							$class          = ( $item->published ) ? '' : ' item_unpublished';
							$ordering       = ( $listOrder == 'ordering' );
							$canCreate      = $user->authorise( 'core.create', 'com_obsocialsubmit' );
							$canEdit        = $user->authorise( 'core.edit', 'com_obsocialsubmit' );
							$canCheckin     = $user->authorise( 'core.manage', 'com_checkin' ) || $item->checked_out == $user->get( 'id' ) || $item->checked_out == 0;
							$canChange      = $user->authorise( 'core.edit.state', 'com_obsocialsubmit' ) && $canCheckin;
							?>
							<tr class="row<?php echo $i % 2;
							echo $class; ?>" sortable-group-id="<?php echo $item->addon_type; ?>">
								<?php if ( ! $isJ25 ) { ?>
									<!--<td class="order nowrap center hidden-phone">
									<?php if ( $canChange ) :
										$disableClassName = '';
										$disabledLabel    = '';
										if ( ! $saveOrder ) :
											$disabledLabel    = JText::_( 'JORDERINGDISABLED' );
											$disableClassName = 'inactive tip-top';
										endif; ?>
										<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>" title="<?php echo $disabledLabel ?>">
								<i class="icon-menu"></i>
							</span>
										<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order" />
									<?php else : ?>
										<span class="sortable-handler inactive">
								<i class="icon-menu"></i>
							</span>
									<?php endif; ?>
								</td>-->
								<?php } ?>
								<td width="1%" class="hidden-phone">
									<?php echo JHtml::_( 'grid.id', $i, $item->id ); ?>
								</td>
								<td class="center">
									<div class="btn-group">
										<?php echo JHtml::_( 'jgrid.published', $item->published, $i, 'adapters.' ); ?>
										<?php
										if ( ! $isJ25 ) :
											?>
											<button class="dropdown-toggle btn btn-micro" data-toggle="dropdown">
												<i class="caret"></i>
												<span class="element-invisible">Actions for: <?php echo $item->title; ?></span>
											</button>
											<ul class="dropdown-menu">
												<li>
													<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i; ?>','adapters.duplicate')">
														<span class="icon-save-copy"></span> <?php echo JText::_( "JTOOLBAR_DUPLICATE" ); ?>
													</a>
												</li>
												<li>
													<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i; ?>','adapters.delete')">
														<span class="icon-trash"></span> <?php echo JText::_( "JTOOLBAR_DELETE" ); ?>
													</a>
												</li>
											</ul>
										<?php endif; ?>
									</div>
								</td>
								<td class="<?php echo $item->addon; ?>">
										<div class="btn-group obss-addon-label">
											<span class="label label-default "><?php echo '#' . (int) $item->id; ?>&nbsp;</span>
											<span class="label label-info ob_addon">&nbsp;<?php echo $item->addon; ?></span>
										</div>
										<?php if ( $item->checked_out ) : ?>
											<?php echo JHtml::_( 'jgrid.checkedout', $i, $item->checked_out, $item->checked_out_time, 'adapters.', $canCheckin ); ?>
										<?php endif; ?>
										<?php if ( $canEdit ) : ?>
											<a href="<?php echo JRoute::_( 'index.php?option=com_obsocialsubmit&task=adapter.edit&id=' . (int) $item->id ); ?>">
												<?php echo $this->escape( $item->title ); ?></a>
										<?php else : ?>
											<?php echo $this->escape( $item->title ); ?>
										<?php endif; ?>

										<?php if ( ! empty( $item->note ) ) : ?>
											<div class="small">
												<?php echo JText::sprintf( 'JGLOBAL_LIST_NOTE', $this->escape( $item->note ) ); ?>
											</div>
										<?php endif; ?>

										<?php /* if ( ! empty( $item->description ) ) : ?>
											<div class="small">
												<?php echo $this->escape( $item->description ); ?>
											</div>
										<?php endif; */
										?>

								</td>
								<td class="small select_connections">
									<div class="obss-btn-share-wrap">
										<a class="btn btn-micro btn-show-networks hasTooltip" href="javascript:obssShowNetworks(<?php echo $i; ?>);"  title="" data-original-title="<?php echo JText::_('COM_OBSOCIALSUBMIT_SELECT_NETWORKS');?>"><i class="icon-share"></i></a>
										<div class="obss-network-container">
											<?php echo JHtml::_( 'obsocialsubmit.selectnetwork', $item, $i, $connections, 2 ); ?>
										</div>
									</div>
								</td>
								<td class="small hidden-phone">
									<div class="obss-selected-networks">
									<?php echo JHtml::_( 'obsocialsubmit.selectnetwork', $item, $i, $connections, 1 ); ?>
									<a class="btn btn-micro ob_toggle hasTooltip ob_toggle_<?php echo $i; ?>" href="javascript:runToggle(<?php echo $i; ?>);"><i class="icon-folder-open"></i></a>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

					<?php //Load the batch processing form. ?>
					<?php //echo $this->loadTemplate( 'batch' ); ?>
					<?php echo $this->loadTemplate( 'select_connects' ); ?>
					<?php echo $this->loadTemplate( 'select' ); ?>
					<input type="hidden" name="task" value="" />
					<input type="hidden" name="boxchecked" value="0" />
					<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
					<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
					<?php echo JHtml::_( 'form.token' ); ?>
				</div>
			</form>
		</div>
		<div class="span4 form-horizontal">
			<?php echo obSocialSubmitHelper::versionNotify(); ?>
			<!-- MSG -->
			<?php echo $this->loadTemplate( 'msg' ); ?>

			<?php echo $this->loadTemplate( 'infor' ); ?>
		</div>
	</div>
</div><!--div #foobla-->

<script type="text/javascript">
	function obssShowNetworks(index) {
		console.log(index);
		jQuery('#cids2_' + index).toggleClass('chzn-with-drop');
	}
	/*Check item unpublished*/
	jQuery(document).ready(function () {
		jQuery(".item_unpublished .ob_toggle").addClass("ob_hide");
		jQuery(".item_unpublished .ob_toggle").find('i').removeClass('fa-minus').addClass("fa-plus");
	});
	function runToggle(id) {
		jQuery(document).ready(function () {
			id = parseInt(id);
			var obclass = jQuery(".ob_toggle_" + id).attr("class");
			var checkClass = obclass.search("ob_hide");
			if (checkClass >= 0) {
				jQuery(".ob_toggle_" + id).removeClass("ob_hide").addClass("ob_show");
				jQuery(".ob_toggle_" + id).find('i').removeClass('fa-plus').addClass("fa-minus");
			} else {
				jQuery(".ob_toggle_" + id).removeClass("ob_show").addClass("ob_hide");
				jQuery(".ob_toggle_" + id).find('i').removeClass('fa-minus').addClass("fa-plus");
			}

			jQuery("#cids_" + id).slideToggle("slow");
		});
	}

	function obssSelectNetwork(el, index) {
		var cf = confirm("<?php echo JText::_("COM_OBSOCIALSUBMIT_SET_CONNECT_CONFIRM_MSG");?>");
		if (!cf) return;
		e = jQuery(el);
		aid = jQuery('#cb' + index).val();
		cid = e.parent().attr('data');
		var data = 'aid=' + aid + '&cid=' + cid + '&ac=c';
		jQuery.post(
			'index.php?option=com_obsocialsubmit&task=adapters.set_connect',
			data,
			function (data, textStatus, jqXHR) {
				li = e.removeAttr('onclick').parent().attr('class', 'search-choice');
				jQuery('#cids_' + index + ' ul.chzn-choices').append(li);
				jQuery('#cids_' + index).removeClass('chzn-with-drop');
			},
			'json'
		);
	}

	function obssUnSelectNetwork(el, index) {
		var cf = confirm("<?php echo JText::_("COM_OBSOCIALSUBMIT_SET_CONNECT_CONFIRM_MSG");?>");
		if (!cf) return;
		e = jQuery(el).parent();
		aid = jQuery('#cb' + index).val();
		cid = e.attr('data');
		var data = 'aid=' + aid + '&cid=' + cid + '&ac=d';
		jQuery.post(
			'index.php?option=com_obsocialsubmit&task=adapters.set_connect',
			data,
			function (data, textStatus, jqXHR) {
				var e = jQuery(el).prev().attr('onclick', 'obssSelectNetwork(this,' + index + ');return false;').parent().attr('class', 'active-result');
				jQuery('#cids2_' + index + ' ul.chzn-results').append(e);
			},
			'json'
		);
	}

	function obssAddNetwork(index) {
		jQuery('#cids_' + index).toggleClass('chzn-with-drop');
	}

	jQuery(document).click(function (event) {
		var target = jQuery(event.target);
		if (!jQuery(target).hasClass('btn-show-networks')) {
			jQuery('.obss_network_select_box').removeClass('chzn-with-drop');
		}
	});

</script>
