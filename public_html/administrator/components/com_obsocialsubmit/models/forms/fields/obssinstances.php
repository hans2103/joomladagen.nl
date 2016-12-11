<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined( 'JPATH_PLATFORM' ) or die;

JFormHelper::loadFieldClass( 'list' );

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.4
 */

jimport( 'joomla.form.fields.checkboxes' );
require_once JPATH_SITE . '/libraries/joomla/form/fields/checkboxes.php';

class JFormFieldObSSInstances extends JFormFieldCheckboxes {
	/**
	 * The field type.
	 *
	 * @var    string
	 * @since  11.4
	 */
	protected $type = 'obSSInstances';

	protected function getInput() {
		$connections = $this->getInstances();
		?>
		<ul class="unstyled">
			<?php
			$classes = array(
				'twitter'          => 'fa-twitter-square',
				'facebook'         => 'fa-facebook-square',
				'facebookpages'    => 'fa-facebook-square',
				'facebookgroup'    => 'fa-facebook-square',
				'linkedin'         => 'fa-linkedin-square',
				'linkedingroup'    => 'fa-linkedin-square',
				'VKontakte'        => 'fa-vk',
				'googleplusmoment' => 'fa-google-plus-square'
			);
			$cids_default = array( '4', '5', '6' );
			foreach ( $connections as $connection ) {
				if ( ! $connection->published ) {
					continue;
				}
				$in_array   = in_array( $connection->id, $this->value );
				$checked    = $in_array ? ' checked="checked" ' : '';
				$class_icon = $in_array ? 'icon-publish' : 'icon-unpublish';

				if ( ! JRequest::getVar( 'id' ) ) {
					$checked    = 'checked="checked"';
					$class_icon = 'icon-publish';
				}
				?>
				<li>
					<label>
					<span class="btn btn-micro jgrid hasTooltip obss_con" title="">
						<i class="<?php echo $class_icon; ?>"></i>
						<input name="<?php echo $this->name; ?>" type="checkbox" style="display: none" value="<?php echo $connection->id; ?>" <?php echo $checked; ?>/>
					</span>
						<a href="index.php?option=com_obsocialsubmit&task=connection.edit&id=<?php echo $connection->id; ?>"><i class="fa-fw fa <?php echo $classes[$connection->addon]; ?>"></i> <?php echo $connection->title; ?>
						</a>
					</label>
				</li>
			<?php
			}
			?>
		</ul>
		<script type="text/javascript">
			jQuery(document).ready(function () {
				jQuery('.obss_con input[type="checkbox"]').change(function (e) {
					var ei = jQuery(this).parent().children("i").get(0);
					if (jQuery(this).is(':checked')) {
						ei.addClass('icon-publish');
						ei.removeClass('icon-unpublish');
					} else {
						ei.removeClass('icon-publish');
						ei.addClass('icon-unpublish');
					}
				});
			});
		</script>
	<?php

	}

	protected function getInstances() {
		$app        = JFactory::getApplication();
		$options    = array();
		$addon_type = $this->element['addontype'];
		if ( ! empty( $addon_type ) ) {
			$db    = JFactory::getDbo();
			$query = $db->getQuery( true );
			$query->select( '*' );
			$query->from( '#__obsocialsubmit_instances' );
			$query->where( 'addon_type 	= ' . $db->q( $addon_type ) );
			$query->where( 'published 	= 1' );
			$query->order( 'ordering, title' );
			$db->setQuery( $query );
			$options = $db->loadObjectList();
		} else {
			$app->enqueueMessage( JText::_( 'COM_OBSOCIALSUBMIT_FORM_FIELDS_OBSSINSTANCES_ADDON_TYPE_EMPTY' ), 'error' );
		}

		return $options;
	}


	protected function getOptions() {
		$app        = JFactory::getApplication();
		$options    = array();
		$addon_type = $this->element['addontype'];
		if ( ! empty( $addon_type ) ) {
			$db    = JFactory::getDbo();
			$query = $db->getQuery( true );
			$query->select( '*' );
			$query->from( '#__obsocialsubmit_instances' );
			$query->where( 'addon_type 	= ' . $db->q( $addon_type ) );
			$query->where( 'published 	= 1' );
			$query->order( 'ordering, title' );
			$db->setQuery( $query );
			$instances = $db->loadObjectList();

			foreach ( $instances as $instance ) {
				$link = 'index.php?option=com_obsocialsubmit&view=connection&layout=edit&id=' . $instance->id;
				$tmp  = JHtml::_( 'select.option', (string) $instance->id, '[' . $instance->addon . '] <a href="' . $link . '" target="blank">' . $instance->title . '</a>', 'value', 'text' );
				// Set some option attributes.
				$tmp->class   = '';
				$tmp->checked = false;
				// Set some JavaScript option attributes.
				$tmp->onclick  = '';
				$tmp->onchange = '';
				$options[]     = $tmp;
			}
		} else {
			$app->enqueueMessage( JText::_( 'COM_OBSOCIALSUBMIT_FORM_FIELDS_OBSSINSTANCES_ADDON_TYPE_EMPTY' ), 'error' );
		}

		return $options;
	}
}