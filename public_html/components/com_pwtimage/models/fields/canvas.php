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
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Layout\FileLayout;

defined('_JEXEC') or die;

/**
 * The PWT Image form field Image.
 *
 * @since  1.0
 */
class PwtimageFormFieldCanvas extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 * @since  1.0
	 */
	protected $type = 'Pwtcanvas';

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

		// Setup variables for display.
		$html        = array();

		$ratio       = !isset($this->element['ratio']) ? '' : (string) $this->element['ratio'];
		$width       = !isset($this->element['width']) ? '400' : (string) $this->element['width'];
		$sourcePath  = !isset($this->element['sourcePath']) ? $parameters->get('sourcePath', '/images') : (string) $this->element['sourcePath'];
		$subPath     = !isset($this->element['subPath']) ? $parameters->get('subPath', '{year}/{month}') : (string) $this->element['subPath'];
		$showFolder  = !isset($this->element['showFolder']) ? $parameters->get('showFolder', false) : ((string) $this->element['showFolder'] === 'false' ? false : true);
		$showTools   = !isset($this->element['showTools']) ? $parameters->get('showTools', true) : ((string) $this->element['showTools'] === 'false' ? false : true);
		$activePage  = !isset($this->element['activePage']) ? 'upload' : (string) $this->element['activePage'];
		$multiple    = !isset($this->element['multiple']) ? false : ((string) $this->element['multiple'] === 'false' ? false : true);
		$showGallery = !isset($this->element['showGallery']) ? $parameters->get('showGallery', false) : ((string) $this->element['showGallery'] === 'false' ? false : true);
		$showHelp    = !isset($this->element['showHelp']) ? $parameters->get('showHelp', true) : ((string) $this->element['showHelp'] === 'false' ? false : true);

		if (!is_array($this->value))
		{
			$this->value = (array) $this->value;
		}

		$cropperLayout = new FileLayout('cropper', JPATH_ROOT . '/components/com_pwtimage/layouts');

		foreach ($this->value as $value)
		{
			// Get a unique ID for each image
			$modalId = uniqid();

			// Check if the image exists
			if (JFile::exists('../' . $value))
			{
				$value = '../' . $value;
			}

			// Set the actions
			$canDo = ContentHelper::getActions('com_pwtimage');

			// Set the PWT Image data
			$data = array(
				'id'           => (string) $modalId,
				'ratio'        => $ratio,
				'width'        => $width,
				'sourcePath'   => $sourcePath,
				'subPath'      => $subPath,
				'showFolder'   => $showFolder,
				'showTools'    => $showTools,
				'activePage'   => $activePage,
				'imagePreview' => $value,
				'multiple'     => $multiple,
				'showGallery'  => $showGallery,
				'showHelp'     => $showHelp,
				'canDo'        => $canDo
			);

			// Render PWT Image
			$html[] = $cropperLayout->render($data);

			// The class='required' for client side validation
			$class = array();

			if ($this->required)
			{
				$class[] = 'required';
				$class[] = 'modal-value';
			}

			$html[] = '<input type="hidden" id="' . $modalId . '_value" class="' . implode(' ', $class) . '" name="' . $this->name . '" value="' . $value . '" />';
		}

		return implode("\n", $html);
	}
}
