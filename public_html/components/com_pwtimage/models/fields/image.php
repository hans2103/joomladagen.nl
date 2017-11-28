<?php
/**
 * @package    Pwtimage
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2017 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Layout\FileLayout;

defined('_JEXEC') or die;

/**
 * The PWT Image form field select button.
 *
 * This creates a button to open PWT Image in a modal window
 *
 * @since  1.0
 */
class PwtimageFormFieldImage extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 * @since  1.0
	 */
	protected $type = 'Pwtimage';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.0
	 */
	protected function getInput()
	{
		// Load the component parameters
		$parameters = ComponentHelper::getParams('com_pwtimage');

		$ratio       = !isset($this->element['ratio']) ? '' : (string) $this->element['ratio'];
		$width       = !isset($this->element['width']) ? '400' : (string) $this->element['width'];
		$sourcePath  = !isset($this->element['sourcePath']) ? $parameters->get('sourcePath', '/images') : (string) $this->element['sourcePath'];
		$subPath     = !isset($this->element['subPath']) ? $parameters->get('subPath', '{year}/{month}') : (string) $this->element['subPath'];
		$showFolder  = !isset($this->element['showFolder']) ? (bool) $parameters->get('showFolder', false) : ((string) $this->element['showFolder'] === 'false' ? false : true);
		$showTools   = !isset($this->element['showTools']) ? (bool) $parameters->get('showTools', true) : ((string) $this->element['showTools'] === 'false' ? false : true);
		$activePage  = !isset($this->element['activePage']) ? 'upload' : (string) $this->element['activePage'];
		$showGallery = !isset($this->element['showGallery']) ? (bool) $parameters->get('showGallery', false) : ((string) $this->element['showGallery'] === 'false' ? false : true);
		$showHelp    = !isset($this->element['showHelp']) ? (bool) $parameters->get('showHelp', true) : ((string) $this->element['showHelp'] === 'false' ? false : true);

		// Set the actions
		$canDo = ContentHelper::getActions('com_pwtimage');

		// Set the PWT Image data
		$data = array(
			'ratio'        => $ratio,
			'width'        => $width,
			'sourcePath'   => $sourcePath,
			'subPath'      => $subPath,
			'showFolder'   => $showFolder,
			'showTools'    => $showTools,
			'activePage'   => $activePage,
			'imagePreview' => $this->value,
			'multiple'     => false,
			'showGallery'  => $showGallery,
			'showHelp'     => $showHelp,
			'target'       => $this->getName($this->fieldname),
			'value'        => $this->value,
			'tokenName'    => 'sessionId',
			'tokenValue'   => Factory::getSession()->getId(),
			'canDo'        => $canDo
		);

		$buttonLayout = new FileLayout('button', JPATH_ROOT . '/components/com_pwtimage/layouts');

		return $buttonLayout->render($data);
	}
}
