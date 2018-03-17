<?php
/**
 * @package    Pwtseo
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2018 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Perfect SEO field
 * The field that is added to the content form
 *
 * @since  1.0
 */
class JFormFieldPWTSeo extends FormField
{
	/**
	 * A Registry object holding the parameters for the plugin
	 *
	 * @var    Registry
	 *
	 * @since  1.0
	 */
	private $params;

	/**
	 * Constructor for the field, we use this to get the plugin params in our field
	 *
	 * @param   JForm $form The form to attach to the form field object
	 *
	 * @since 1.0
	 */
	public function __construct($form = null)
	{
		$this->params = new Registry(PluginHelper::getPlugin('system', 'pwtseo')->params);

		parent::__construct($form);
	}

	/**
	 * Get the label of the PWTSeo Field. We abuse this to create our own layout.
	 *
	 * @return string The label - the left side of the panel
	 *
	 * @since 1.0
	 */
	protected function getLabel()
	{
		return '';
	}

	/**
	 * Get the html/view of the input field. We abuse this to create our own layout.
	 *
	 * @return string The input - the right side of the panel
	 *
	 * @since 1.0
	 */
	protected function getInput()
	{
		ob_start();

		include JPATH_PLUGINS . '/system/pwtseo/tmpl/serp.php';

		// By loading another form model, we can trigger the onContentPrepareForm on our actual seo instead of the original
		$form = new Form('com_pwtseo');

		$form->loadFile(JPATH_PLUGINS . '/system/pwtseo/form/form.xml', false);
		$this->form->loadFile(JPATH_PLUGINS . '/system/pwtseo/form/form.xml', false);

		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onContentPrepareForm', array(&$form, array()));

		// Now we update the original form with all our fields
		$fieldsets = $form->getFieldsets();

		foreach ($fieldsets as $set)
		{
			$fields = $form->getFieldset($set->name);

			/** @var FormField $field */
			foreach ($fields as $field)
			{
				if (method_exists($field, 'getAttribute') === false)
				{
					continue;
				}

				$xml = $form->getFieldXml($field->getAttribute('name'), 'seo');

				$this->form->setField($xml, '', true, $set->name);
			}
		}

		echo $this->form->renderFieldset('left-side');

		if ($this->params->get('advanced_mode'))
		{
			echo $this->form->renderFieldset('advanced_og');
		}
		else
		{
			echo $this->form->renderFieldset('basic_og');
		}

		include JPATH_PLUGINS . '/system/pwtseo/tmpl/requirements.php';

		$sHTML = ob_get_clean();

		return $sHTML;
	}
}