<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */
 
defined( 'JPATH_BASE' ) or die;

/**
 * Extended Utility class for batch processing widgets.
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       1.7
 */
abstract class JHtmlOBSocialSubmit {
	/**
	 * Display a batch widget for the access level selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @since   1.7
	 */
	public static function instances( $name = 'instances', $type = 'intern', $value = '', $id = '' ) {
		if ( ! in_array( $type, array( 'intern', 'extern' ) ) ) {
			return JText::_( 'COM_OBSOCIAL_SUBMIT_TYPE_NOT_EXISTS' );
		}

		// Get the field options.
		$id  = ( $id ) ? $id : $name;
		$db  = JFactory::getDbo();
		$sql = "SELECT * FROM #__obsocialsubmit_instances WHERE `addon_type`='{$type}' and `published`=1 ORDER BY `title`";
		$db->setQuery( $sql );
		$rows    = $db->loadObjectList();
		$options = array();
		foreach ( $rows as $row ) {
			$options[] = JHtml::_( 'select.option', $row->id, '[' . $row->id . '][' . $row->addon . ']' . $row->title, 'value', 'text' );
		}
		$html = array();

		$attr = '';
		$attr .= ' multiple="multiple"';
		$html[] = JHtml::_( 'select.genericlist', $options, $name, trim( $attr ), 'value', 'text', $value, $id );

		return implode( $html );
	}

	public static function batch_action() {
		global $isJ25;
		// Create the copy/move options.
		$options = array(
			JHtml::_( 'select.option', 'connect', JText::_( 'COM_OBSOCIALSUBMIT_HTML_BATCH_CONNECT' ) ),
			JHtml::_( 'select.option', 'disconnect', JText::_( 'COM_OBSOCIALSUBMIT_HTML_BATCH_DISCONNECT' ) )
		);

		// Create the batch selector to change select the category by which to move or copy.
		$html =
			'
			<div class="control-group">
				<div class="control-label">
					<label id="batch-choose-action-lbl" for="batch-choose-action">' .
			JText::_( 'COM_OBSOCIALSUBMIT_HTML_BATCH_ACTION_LABEL' ) .
			'</label>' .
			'</div>
			<div style="clear:both;"></div>
			<div class="controls">
				<div id="batch-connect-disconnect" class="control-group radio">' .
			( ( $isJ25 ) ? '<fieldset id="batch-connect-disconnect" class="radio btn-group">' : '' ) .
			JHtml::_( 'select.radiolist', $options, 'batch[action]', '', 'value', 'text', 'connect' ) .
			( ( $isJ25 ) ? '</fieldset>' : '' ) .
			'</div>
		</div>
	</div>';

		return $html;

	}

	public static function debug( $value = 0, $i, $canChange = true, $task_prefix = '' ) {
		// Array of image, task, title, action
		$states = array(
			0 => array( 'checkbox-unchecked', $task_prefix . 'debug', 'COM_OBSOCIALSUBMIT_DEBUG', 'COM_OBSOCIALSUBMIT_TOGGLE_TO_TURN_ON_DEBUG' ),
			1 => array( 'checkbox', $task_prefix . 'undebug', 'COM_OBSOCIALSUBMIT_UNDEBUG', 'COM_OBSOCIALSUBMIT_TOGGLE_TO_TURN_OFF_DEBUG' ),
		);
		$state  = JArrayHelper::getValue( $states, (int) $value, $states[1] );
		$icon   = $state[0];
		if ( $canChange ) {
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip' . ( $value == 1 ? ' active' : '' ) . '" data-original-title="' . JText::_( $state[3] ) . '" title="' . JText::_( $state[3] ) . '" data-toggle="tooltip"><i class="icon-'
				. $icon . '"></i></a>';
		}

		return $html;
	}

	public static function status( $value = 0, $i, $canChange = true, $task_prefix = '' ) {
		// Array of image, task, title, action
		$task_prefix2 = strtoupper( str_replace( '.', '', $task_prefix ) );
		$states       = array(
			0 => array( 'unpublish', $task_prefix . 'on', 'COM_OBSOCIALSUBMIT_' . $task_prefix2 . 'ON', 'COM_OBSOCIALSUBMIT_' . $task_prefix2 . 'ON_DESC' ),
			1 => array( 'publish', $task_prefix . 'off', 'COM_OBSOCIALSUBMIT_' . $task_prefix2 . 'OFF', 'COM_OBSOCIALSUBMIT_' . $task_prefix2 . 'OFF_DESC' ),
		);
		$state        = JArrayHelper::getValue( $states, (int) $value, $states[1] );
		$icon         = $state[0];
		if ( $canChange ) {
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip' . ( $value == 1 ? ' active' : '' ) . '" data-original-title="' . JText::_( $state[3] ) . '" title="' . JText::_( $state[3] ) . '" data-toggle="tooltip"><i class="icon-'
				. $icon . '"></i></a>';
		}

		return $html;
	}

	public static function button( $i, $task = '', $text = 'button', $class = 'btn btn-small' ) {
		$html = '<a class="' . $class . '" onclick="return listItemTask(\'cb' . $i . '\',\'' . $task . '\')">' . $text . '</a>';

		return $html;
	}

	public static function selectnetwork( $item, $index, $connections, $option = 0 ) {
		$icons           = array(
			'twitter'  => 'fa-twitter-square',
			'facebook' => 'fa-facebook-square',
			'linkedin' => 'fa-linkedin-square'
		);
		$icon_default    = '';
		$keys            = array_keys( $connections );
		$keys_selected   = preg_split( '/\s*,\s*/i', $item->cids, null, 1 );
		$keys_unselected = array_diff( $keys, $keys_selected );

		ob_start();
		// chzn-with-drop
		if ( $option != 2 ) :
			?>
			<div id="cids_<?php echo $index; ?>" class="obss_network_select_box chzn-container chzn-container-multi">
				<ul class="chzn-choices">
					<?php
					if ( ! empty( $connections ) && ! empty( $keys_selected ) ) {
						foreach ( $keys_selected as $key ) {
							if ( array_key_exists( $key, $connections ) ) {
								$connection      = $connections[$key];
								$connection_icon = array_key_exists( $connection->addon, $icons ) ? $icons[$connection->addon] : $icon_default;
								$link            = 'index.php?option=com_obsocialsubmit&task=connection.edit&id=' . $connection->id;
								echo '<li class="search-choice" data="' . $connection->id . '">'
									. '<a href="' . $link . '" target="_blank"><i class="fa-fw fa ' . $connection_icon . '"></i> <span>' . $connection->title . '</span></a>'
									. '<a class="search-choice-close" onclick="obssUnSelectNetwork(this,' . $index . ')"></a></li>';
							}
						}
					}
					?>
				</ul>
			</div>
		<?php
		endif;

		if ( $option != 1 ) :
			?>
			<div id="cids2_<?php echo $index; ?>" class="obss_network_select_box chzn-container chzn-container-multi">
				<div class="chzn-drop">
					<ul class="chzn-results">
						<?php
						if ( ! empty( $connections ) && ! empty( $keys_unselected ) ) {
							foreach ( $keys_unselected as $key ) {
								if ( array_key_exists( $key, $connections ) ) {
									$connection      = $connections[$key];
									$connection_icon = array_key_exists( $connection->addon, $icons ) ? $icons[$connection->addon] : $icon_default;
									$link            = 'index.php?option=com_obsocialsubmit&task=connection.edit&id=' . $connection->id;
									echo '<li class="active-result" data="' . $connection->id . '">'
										. '<a href="' . $link . '" target="_blank" onclick="obssSelectNetwork(this, ' . $index . ');return false;"><i class="fa-fw fa ' . $connection_icon . '"></i> <span>' . $connection->title . '</span></a>'
										. '<a class="search-choice-close" onclick="obssUnSelectNetwork(this,' . $index . ')"></a></li>';
								}
							}
						}
						?>
					</ul>
					<?php
					global $isJ25;
					$add_network_btn = '';
					if ( $isJ25 ) {
						?>
						<a onclick="SqueezeBox.fromElement(this, {parse:'rel'}); return false;" href="#selectModalconnect" class="btn btn-small btn-success modal btn-add-network" rel="{size: {x: 680, y: 400}}">
							<?php echo JText::_( 'COM_OBSOCIALSUBMIT_ADD_NETWORK' ); ?>
						</a>
					<?php
					} else {
						?>
						<hr/>
						<a href="javascript:void(0)" class="btn-add-network" data-toggle="modal" data-target="#selectModalconnect"><i class="fa fa-plus fa-2"></i><?php echo JText::_( 'COM_OBSOCIALSUBMIT_ADD_NETWORK' ); ?></a>
					<?php
					}
					?>
				</div>
				<!--
				<div class="ob_postto">
					<span class="btn btn-small btn-info btn-show-networks" onclick="obssShowNetworks(<?php echo $index; ?>);"><?php echo JText::_( 'COM_OBSOCIALSUBMIT_POST_TO' ); ?>&nbsp;<i class="caret"></i></span>
					<span class="ob_toggle ob_toggle_<?php echo $index; ?> " onclick="runToggle(<?php echo $index; ?>);">
						<i class="fa fa-minus"></i>
						<?php echo JText::_( "COM_OBSOCIALSUBMIT_TOGGLE" ); ?>
					</span>
				</div>
				-->
			</div>
		<?php
		endif;
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
}