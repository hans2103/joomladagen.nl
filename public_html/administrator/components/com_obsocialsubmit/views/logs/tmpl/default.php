<?php
/**
 * @package        obSocialSubmit
 * @author         foobla.com.
 * @copyright      Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license        GNU/GPL
 */

defined( '_JEXEC' ) or die;
global $isJ25;
$filename = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_obsocialsubmit' . DS . 'helpers' . DS . 'functions.php';
if ( is_file( $filename ) ) {
	require_once $filename;
}
$obsshelp = new obSSHelper;
// $jv = new JVersion();
// $isJ25 = $jv->RELEASE=='2.5';
if ( ! $isJ25 ) {
	JHtml::_( 'bootstrap.tooltip' );
	JHtml::_( 'dropdown.init' );
	JHtml::_( 'formbehavior.chosen', 'select' );
}
JHtml::_( 'behavior.multiselect' );

// $client		= $this->state->get('filter.client_id') ? 'administrator' : 'site';
$user = JFactory::getUser();
$listOrder = $this->escape( $this->state->get( 'list.ordering' ) );
$listDirn = $this->escape( $this->state->get( 'list.direction' ) );
$canOrder = $user->authorise( 'core.edit.state', 'com_obsocialsubmit' );
$saveOrder = $listOrder == 'i.ordering';
$config = JFactory::getConfig();
$offset = $config->get('offset');
?>
<form action="<?php echo JRoute::_( 'index.php?option=com_obsocialsubmit&view=logs' ); ?>" method="post" name="adminForm" id="adminForm">
	<div id="foobla">
		<div class="row-fluid">
			<?php if (! empty( $this->sidebar )): ?>
			<div id="j-sidebar-container" class="span2">
				<?php echo $this->sidebar; ?>
			</div>
			<div id="j-main-container" class="span10">
				<?php else : ?>
				<div id="j-main-container">
					<?php endif; ?>
					<div id="filter-bar" class="btn-toolbar">
						<div class="filter-select fltrt pull-left">
							<select name="filter_adapter_type" class="inputbox" onchange="this.form.submit()">
								<option value=""><?php echo JText::_( 'COM_OBSOCIALSUBMIT_ADAPTER_TYPE' ); ?></option>
								<?php
								echo JHtml::_( 'select.options', $this->adaptertypes, 'element', 'element', $this->state->get( 'filter.adapter_type' ) );?>
							</select>
							<select name="filter_connection_type" class="inputbox" onchange="this.form.submit()">
								<option value=""><?php echo JText::_( 'COM_OBSOCIALSUBMIT_CONNECTION_TYPE' ); ?></option>
								<?php
								echo JHtml::_( 'select.options', $this->connectiontypes, 'element', 'element', $this->state->get( 'filter.connection_type' ) );?>
							</select>
							<select name="filter_processed" class="inputbox" onchange="this.form.submit()">
								<option value=""><?php echo JText::_( 'JOPTION_SELECT_PROCESSED' ); ?></option>
								<?php echo JHtml::_( 'select.options', obSocialSubmitHelper::getProcessOptions(), 'value', 'text', $this->state->get( 'filter.processed' ) ); ?>
							</select>
							<select name="filter_status" class="inputbox" onchange="this.form.submit()">
								<option value=""><?php echo JText::_( 'JOPTION_SELECT_PUBLISHED' ); ?></option>
								<?php echo JHtml::_( 'select.options', obSocialSubmitHelper::getStateOptions(), 'value', 'text', $this->state->get( 'filter.status' ) ); ?>
							</select>
						</div>
						<div class="btn-group pull-right hidden-phone">
							<label for="limit" class="element-invisible"><?php echo JText::_( 'JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC' ); ?></label>
							<?php echo $this->pagination->getLimitBox(); ?>
						</div>
					</div>
					<div class="clearfix"></div>
					<table class="table table-striped adminlist" id="connectionList">
						<thead>
						<tr>
							<th width="1%" class="hidden-phone">
								<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_( 'JGLOBAL_CHECK_ALL' ); ?>" onclick="Joomla.checkAll(this)" />
							</th>
							<th width="1%" class="nowrap center">
								<?php echo JHtml::_( 'grid.sort', 'Item Id', 'l.iid', $listDirn, $listOrder ); ?>
							</th>
							<th class="10%">
								<?php echo JHtml::_( 'grid.sort', 'COM_OBSOCIALSUBMIT_HEADING_ADAPTER', 'a.addon', $listDirn, $listOrder ); ?>
							</th>
							<th width="10%" class="nowrap hidden-phone">
								<?php echo JHtml::_( 'grid.sort', 'COM_OBSOCIALSUBMIT_HEADING_CONNECTION', 'c.addon', $listDirn, $listOrder ); ?>
							</th>
							<th width="10%" class="nowrap hidden-phone">
								<?php echo JHtml::_( 'grid.sort', 'COM_OBSOCIALSUBMIT_HEADING_PUBLISH_TIME', 'l.publish_up', $listDirn, $listOrder ); ?>
							</th>
							<th width="10%" class="nowrap hidden-phone">
								<?php echo JHtml::_( 'grid.sort', 'COM_OBSOCIALSUBMIT_HEADING_PROCESSED', 'l.processed', $listDirn, $listOrder ); ?>
							</th>
							<th width="10%" class="nowrap hidden-phone">
								<?php echo JHtml::_( 'grid.sort', 'COM_OBSOCIALSUBMIT_HEADING_PROCESS_TIME', 'l.process_time', $listDirn, $listOrder ); ?>
							</th>
							<th width="1%" class="nowrap center hidden-phone">
								<?php echo JHtml::_( 'grid.sort', 'JSTATUS', 'l.status', $listDirn, $listOrder ); ?>
							</th>
						</tr>
						</thead>
						<tfoot>
						<tr>
							<td colspan="10">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
						</tfoot>
						<tbody>
						<?php foreach ( $this->items as $i => $item ) :
							$ordering   = ( $listOrder == 'ordering' );
							$processed  = ( $item->processed > 0 ) ? 1 : 0;
							$canCreate  = $user->authorise( 'core.create', 'com_obsocialsubmit' );
							$canEdit    = $user->authorise( 'core.edit', 'com_obsocialsubmit' );
							$canCheckin = $user->authorise( 'core.manage', 'com_checkin' ) || $item->checked_out == $user->get( 'id' ) || $item->checked_out == 0;
							$canChange  = $user->authorise( 'core.edit.state', 'com_obsocialsubmit' ) && $canCheckin;
							$item_title = obSocialSubmitHelper::getItemTitle( $item->iid, $item->adapter_type );
							/*
											require_once JPATH_SITE.DS.'plugins'.DS.'obss_intern'.DS.$item->adapter_type.DS.$item->adapter_type.'.php';
											if(method_exists('OBSSInAddon'.$item->adapter_type,'getItemTitle')){
												$item_title = call_user_func_array(array('OBSSInAddon'.$item->adapter_type,'getItemTitle'),array($item->iid) );
											}
							*/
							?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="center hidden-phone">
									<?php echo JHtml::_( 'grid.id', $i, $item->iid . ',' . $item->aid . ',' . $item->cid ); ?>
								</td>
								<td class="has-context nowrap">
									<?php echo $item_title; ?>
								</td>
								<td class="has-context nowrap">
									<div class="pull-left">
										<a target="blank" href="index.php?option=com_obsocialsubmit&task=adapter.edit&id=<?php echo $item->aid; ?>"><?php echo '[' . $item->aid . ']' . $item->atitle; ?></a>

										<div class="small"><?php echo JText::_( 'COM_OBSOCIALSUBMIT_TYPE' ); ?>: <?php echo $item->adapter_type; ?></div>
									</div>
								</td>
								<td class="has-context nowrap">
									<div class="pull-left">
										<a target="blank" href="index.php?option=com_obsocialsubmit&task=connection.edit&id=<?php echo $item->cid; ?>"><?php echo '[' . $item->cid . ']' . $item->ctitle; ?></a>

										<div class="small"><?php echo JText::_( 'COM_OBSOCIALSUBMIT_TYPE' ); ?>: <?php echo $item->connection_type; ?></div>
									</div>
								</td>
								<td class="has-context nowrap">
									<?php
									$publish_up = $obsshelp->date_convert($item->publish_up, 'UTC', 'Y-m-d H:s:i', $offset, 'Y-m-d H:s:i');
									echo '<div>' . str_replace( ' ', '</div><div class="small">', $publish_up ) . '</div>'; ?>
								</td>
								<td class="center hidden-phone">
									<?php echo JHtml::_( 'obsocialsubmit.status', $processed, $i, true, 'logs.process' );
									if ( $item->processed >= 1 ) {
										echo '</br>' . '(' . $item->processed . ')';
									}
									?>
								</td>
								<td class="has-context nowrap">
									<?php echo '<div>' . str_replace( ' ', '</div><div class="small">', $item->process_time ) . '</div>'; ?>
								</td>
								<td class="center">
									<?php echo JHtml::_( 'obsocialsubmit.status', $item->status, $i, true, 'logs.status' ); ?>
								</td>
								<td class="center  nowrap">
									<?php echo JHtml::_( 'obsocialsubmit.button', $i, 'logs.processlog', JText::_( "COM_OBSOCISLSUBMIT_LOGS_PROCESS_NOW" ) ); ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

					<?php //Load the batch processing form. ?>
					<?php echo $this->loadTemplate( 'batch' ); ?>

					<input type="hidden" name="task" value="" />
					<input type="hidden" name="boxchecked" value="0" />
					<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
					<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
					<?php echo JHtml::_( 'form.token' ); ?>
					<div id="appsloading" style="background-image: url(<?php echo JURI::root(); ?>media/jui/img/ajax-loader.gif); background-color: rgba(255, 255, 255, 0.8); position: fixed; z-index: 1000; opacity: 0.8; top: 185px; left: 223.453125px; width: 1087px; height: 183px; display: block; background-position: 50% 15%; background-repeat: no-repeat no-repeat;display:none;"></div>
				</div>
			</div>
		</div>
</form>
