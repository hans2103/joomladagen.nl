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
 * Plugin to generate a payment link.
 *
 * @package  JDiDEAL
 * @since    4.5.0
 */
class plgSystemJdidealpaymentlink extends JPlugin
{
	/**
	 * Application
	 *
	 * @var    JApplicationSite
	 * @since  4.5.0
	 */
	protected $app;

	/**
	 * Load the stylesheet as the last one in the administrator section.
	 *
	 * @return  void
	 *
	 * @since   4.5.0
	 */
	public function onBeforeRender()
	{
		/** @var \Joomla\CMS\Document\HtmlDocument $document */
		$document = JFactory::getDocument();

		if (!$this->app->isClient('administrator')
			|| $document->getType() !== 'html'
			|| stristr($document->getBuffer('component'), 'editor') === false
			|| in_array($this->app->input->getCmd('format'), array('raw', 'json')))
		{
			return;
		}

		// We need to add our own stylesheet as last because otherwise our icons are overwritten
		$url = JUri::getInstance()->getHost() . JUri::root(true);
		$stylelink = '<link href="//' . $url . '/media/com_jdidealgateway/css/jdidealgateway.css" rel="stylesheet" />';
		$document->addCustomTag($stylelink);
	}

	/**
	 * Find and replace tags with a payment link.
	 *
	 * @return  void
	 *
	 * @since   4.5.0
	 */
	public function onAfterRender()
	{
		// Fix the icon in the editor
		if ($this->app->isClient('administrator') && $this->app->input->getCmd('option') !== 'com_rsform')
		{
			// Replace the icon-jdideal to jdicon-jdideal
			$body = $this->app->getBody();
			$body = str_replace('icon-jdideal', 'jdicon-jdideal', $body);
			$this->app->setBody($body);

			return;
		}

		// Do the tag replacement on the frontend
		if (!$this->app->isClient('site'))
		{
			return;
		}

		$body = $this->app->getBody();
		$body = $this->replaceTags($body);
		$this->app->setBody($body);
	}

	/**
	 * Replace the tags.
	 *
	 * @param   string  $text  The body to replace the tags in.
	 *
	 * @return  string  The replaced string.
	 *
	 * @since   4.5.0
	 */
	private function replaceTags($text)
	{
		$regex = '/{jdidealpaymentlink\s([^\}]+)\}/';

		if (!preg_match_all($regex, $text, $matches))
		{
			return $text;
		}

		// URL to use for the link
		$url = '/index.php?option=com_jdidealgateway&view=pay';

		foreach ($matches as $index => $match)
		{
			$tag       = $matches[0][$index];
			$arguments = $this->convertArguments($matches[1][$index]);
			$title     = 'JD iDEAL Gateway';

			// Check if there is a title
			foreach ($arguments as $argument)
			{
				list($name, $value) = explode('=', $argument);

				if ($name === 'title')
				{
					$title = $value;
				}
				else
				{
					$url .= '&' . $name . '=' . $value;
				}
			}

			$link = JHtml::_('link', $url, $title);
			$text = str_replace($tag, $link, $text);
		}

		return $text;
	}

	/**
	 * Convert the arguments to an array.
	 *
	 * @param   string  $arguments  The list of arguments.
	 *
	 * @return  array  List of arguments.
	 *
	 * @since   4.5.0
	 */
	private function convertArguments($arguments)
	{
		$replaced = preg_match_all('/[a-z]+="[^"]+"/', $arguments, $matches);

		// If no replacement was done, don't do anything
		if (!$replaced || $replaced === 0)
		{
			return array();
		}

		// Replace quotes
		$find      = array('"', "'", ' ');
		$replace   = array('', '', '+');
		$arguments = str_replace($find, $replace, $matches[0]);

		return $arguments;
	}
}
