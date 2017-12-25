<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;
/**
 * JD iDEAL Gateway payment Button.
 *
 * @since  1.0
 */
class PlgButtonJdidealpaymentbutton extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	protected $autoloadLanguage = true;
	/**
	 * Display the button.
	 *
	 * @param   string  $name  The name of the button to display.
	 *
	 * @return  object The button to show.
	 *
	 * @since   1.0
	 */
	public function onDisplay($name)
	{
		$link = 'index.php?option=com_jdidealgateway&amp;view=jdidealgateway&amp;layout=button&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1&amp;editor=' . $name;

		$button          = new JObject;
		$button->modal   = true;
		$button->class   = 'btn';
		$button->link    = $link;
		$button->text    = JText::_('PLG_JDIDEALPAYMENTBUTTON_BUTTON');
		$button->name    = 'jdideal';
		$button->options = "{handler: 'iframe', size: {x: 500, y: 350}}";

		return $button;
	}
}