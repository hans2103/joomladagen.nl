<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();
jimport('joomla.form.formfield');

if (file_exists(JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php'))
{
	require_once JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php';
	TjStrapper::loadTjAssets('com_jticketing');
}

/**
 * Class for gettingheader tooltip for each elements
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldNote extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var String
	 * @since 1.6
	 */
	public $type = 'note';

	/**
	 * Get html of the element
	 *
	 * @return  Html
	 *
	 * @since  1.0.0
	 */
	public function getInput()
	{
		$html = '';

		if ($this->id == 'jform_jticketing_google_map_api_note')
		{
			$html .= '<div class="span9 alert alert-info">' . JText::_('COM_JTICKETING_GOOGLE_MAP_API_NOTE') . '</div>';
		}

		$return = $html;

		return $return;
	}
}
