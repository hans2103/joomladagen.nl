<?php
/**
 * @package    Pwtimage
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2017 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

/**
 * PWT image plugin.
 *
 * @since  1.0
 */
class PlgSystemPwtimage extends CMSPlugin
{
	/**
	 * Application  instance
	 *
	 * @var    SiteApplication
	 * @since  1.0
	 */
	protected $app;

	/**
	 * @var    String  base update url, to decide whether to process the event or not
	 *
	 * @since  1.0.0
	 */
	private $baseUrl = 'https://extensions.perfectwebteam.com/pwt-image';

	/**
	 * @var    String  Extension identifier, to retrieve its params
	 *
	 * @since  1.0.0
	 */
	private $extension = 'com_pwtimage';

	/**
	 * @var    String  Extension title, to retrieve its params
	 *
	 * @since  1.0.0
	 */
	private $extensiontitle = 'PWT Image';

	/**
	 * Event method that runs on content preparation
	 *
	 * Turns all media fields to pwtimage.image fields
	 *
	 * @param   Form     $form  The form object
	 * @param   integer  $data  The form data
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		// Check if we get a valid form
		if (!($form instanceof Form))
		{
			$this->_subject->setError(Text::_('JERROR_NOT_A_FORM'));

			return false;
		}

		// Set that field path is not yet set
		$fieldPath = false;

		// Run through the form to find any media fields
		/** @var SimpleXMLElement $item */
		foreach ($form->getXml() as $item)
		{
			// Find the fields
			if (isset($item->fieldset))
			{
				$fields = $item->xpath('fieldset/field');
			}
			elseif (isset($item->field))
			{
				$fields = $item->xpath('field');
			}
			else
			{
				$fields = $item;
			}

			/** @var SimpleXMLElement $field */
			foreach ($fields as $field)
			{
				// Check if we have a media type field
				if (!((string) $field->attributes()->type === 'media'))
				{
					continue;
				}

				$form->setFieldAttribute((string) $field->attributes()->name, 'type', 'pwtimage.image', (string) $item->attributes()->name);

				if (!$fieldPath)
				{
					$form->setFieldAttribute(
						(string) $field->attributes()->name,
						'addfieldpath',
						'components/com_pwtimage/models/fields',
						(string) $item->attributes()->name
					);

					$fieldPath = true;
				}
			}
		}

		return true;
	}

	/**
	 * Replace tags before the content is being displayed.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onAfterRender()
	{
		// Only run on frontend, do not run when we are in AJAX mode
		if ($this->app->isClient('administrator') || $this->app->input->get('format') === 'json')
		{
			return;
		}

		$body = $this->app->getBody();
		$this->replaceTags($body);
		$this->app->setBody($body);
	}

	/**
	 * Replace tags in a given text.
	 *
	 * @param   string  $text    The text to replace.
	 * @param   bool    $remove  Set if the matched string should be replaced.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function replaceTags(&$text, $remove = false)
	{
		$this->replaceImage($text, $remove);
	}

	/**
	 * Replace Image.
	 *
	 * @param   string  $text    The text to replace.
	 * @param   bool    $remove  Set if the matched string should be replaced.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function replaceImage(&$text, $remove)
	{
		if (strpos($text, '{image') !== false)
		{
			$pattern = '/\{image([^\}]+)\}/';

			if (preg_match_all($pattern, $text, $matches))
			{
				foreach ($matches[0] as $i => $match)
				{
					if ($remove)
					{
						$text = str_replace('<p>' . $matches[0][$i] . '</p>', '', $text);
						$text = str_replace($matches[0][$i], '', $text);
					}
					else
					{
						// Set the defaults
						$image   = false;
						$alt     = '';
						$caption = false;

						// Check for placeholders
						$subPattern = '/\s?([a-zA-Z0-9]+)="([^"\\\]*(?:\\\.[^"\\\]*)*)"/';
						preg_match_all($subPattern, $matches[1][$i], $subMatches);

						// Backwards compatible check for path=/my/image
						if (count($subMatches[0]) === 0)
						{
							$path  = trim(str_replace('&nbsp;', '', $matches[1][$i]));
							$image = str_replace('path=', '', $path);
						}
						else
						{
							// Get the image
							$key = array_search('path', $subMatches[1]);

							if ($key !== false)
							{
								$image = str_replace(array('&nbsp;', ' '), array('', '%20'), trim($subMatches[2][$key]));
							}

							// Get the alt-text
							$key = array_search('alt', $subMatches[1]);

							if ($key)
							{
								$alt = htmlentities(trim(str_replace('\"', '"', $subMatches[2][$key])));
							}

							// Get the caption
							$key = array_search('caption', $subMatches[1]);

							if ($key)
							{
								$caption = htmlentities(trim(str_replace('\"', '"', $subMatches[2][$key])));
							}
						}

						$data = array (
							'image'   => $image,
							'alt'     => $alt,
							'caption' => $caption
						);

						$layout = new FileLayout('image', JPATH_SITE . '/components/com_pwtimage/layouts');
						$embed  = $layout->render($data);

						$text = str_replace($matches[0][$i], $embed, $text);
					}
				}
			}
		}
	}

	/**
	 * Adding required headers for successful extension update
	 *
	 * @param   string $url     url from which package is going to be downloaded
	 * @param   array  $headers headers to be sent along the download request (key => value format)
	 *
	 * @return  boolean true    Always true, regardless of success
	 *
	 * @since   1.0.0
	 *
	 * @throws  Exception
	 */
	public function onInstallerBeforePackageDownload(&$url, &$headers)
	{
		// Are we trying to update our own extensions?
		if (strpos($url, $this->baseUrl) !== 0)
		{
			return true;
		}

		// Load language file
		$jLanguage = Factory::getLanguage();
		$jLanguage->load('com_pwtimage', JPATH_ADMINISTRATOR . '/components/com_pwtimage/', 'en-GB', true, true);
		$jLanguage->load('com_pwtimage', JPATH_ADMINISTRATOR . '/components/com_pwtimage/', null, true, false);

		// Get the Download ID from component params
		$downloadId = ComponentHelper::getComponent($this->extension)->params->get('downloadid', '');

		// Set Download ID first
		if (empty($downloadId))
		{
			Factory::getApplication()->enqueueMessage(
				Text::sprintf('COM_PWTIMAGE_DOWNLOAD_ID_REQUIRED',
					$this->extension,
					$this->extensiontitle
				),
				'error'
			);

			return true;
		}
		// Append the Download ID
		else
		{
			$separator = strpos($url, '?') !== false ? '&' : '?';
			$url       .= $separator . 'key=' . $downloadId;
		}

		// Get the clean domain
		$domain = '';

		if (preg_match('/\w+\..{2,3}(?:\..{2,3})?(?:$|(?=\/))/i', Uri::base(), $matches) === 1)
		{
			$domain = $matches[0];
		}

		// Append domain
		$url .= '&domain=' . $domain;

		return true;
	}
}
