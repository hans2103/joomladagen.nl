<?php
/**
 * @package     CSVI
 * @subpackage  SEF
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * SEF helper class for the component.
 *
 * @package     CSVI
 * @subpackage  SEF
 * @since       4.0
 */
class CsviHelperSef
{
	/**
	 * The domain name to use for URLs
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $domainname = null;

	/**
	 * An instance of the CsviHelperTemplate.
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.0
	 */
	private $template = null;

	/**
	 * An instance of the CsviHelperLog.
	 *
	 * @var    CsviHelperLog
	 * @since  6.0
	 */
	private $log = null;

	/**
	 * JDatabaseDriver
	 *
	 * @var    JDatabaseDriver
	 * @since  7.1.0
	 */
	private $db;

	/**
	 * Total number of SEF URLs in the database
	 *
	 * @var    int
	 * @since  7.1.0
	 */
	private $sefCount = 0;

	/**
	 * Constructor.
	 *
	 * @param   CsviHelperSettings  $settings  An instance of CsviHelperSettings.
	 * @param   CsviHelperTemplate  $template  An instance of CsviHelperTemplate.
	 * @param   CsviHelperLog       $log       An instance of CsviHelperLog.
	 *
	 * @since   4.0
	 *
	 * @throws  CsviException
	 */
	public function __construct(CsviHelperSettings $settings, CsviHelperTemplate $template, CsviHelperLog $log)
	{
		$this->domainname = $settings->get('hostname');

		// Make sure we have a valid domain name
		if (filter_var($this->domainname, FILTER_VALIDATE_URL) === false)
		{
			throw new CsviException(JText::_('COM_CSVI_NO_VALID_DOMAIN_NAME_SET'));
		}

		$this->template   = $template;
		$this->log        = $log;
		$this->db         = JFactory::getDbo();

		if ($template->get('emptysefcache', false))
		{
			$this->db->truncateTable('#__csvi_sefurls');
		}

		// Check if there are any SEF URLs in the database
		$this->countSefUrls();
	}

	/**
	 * Count the total number of SEF URLs.
	 *
	 * @return  void
	 *
	 * @since   7.1.0
	 */
	private function countSefUrls()
	{
		$query = $this->db->getQuery(true)
			->select('COUNT(' . $this->db->quoteName('sefurl_id') . ')')
			->from($this->db->quoteName('#__csvi_sefurls'));
		$this->db->setQuery($query);

		$this->sefCount = $this->db->loadResult();
	}

	/**
	 * Create a SEF URL by querying the URL.
	 *
	 * @param   string  $url  The URL to change to SEF.
	 *
	 * @return  string  The SEF URL.
	 *
	 * @since   6.0
	 */
	public function getSefUrl($url)
	{
		if ($this->template->get('exportsef', false))
		{
			$language = substr($this->template->get('language'), 0, 2);
			$language = $language ? '&lang=' . $language : '';

			if ($this->sefCount > 0)
			{
				$sefUrl = $this->loadSefUrl($url . $language);

				if ($sefUrl)
				{
					return $sefUrl;
				}
			}

			$parseUrl = json_encode($url);
			$http     = JHttpFactory::getHttp(null, array('curl', 'stream'));
			$result   = $http->post(
				$this->domainname . '/index.php?option=com_csvi&task=sefs.getsef&format=json' . $language, array('parseurl' => $parseUrl)
			);
			$output   = json_decode($result->body);

			if (is_object($output) && $output->success)
			{
				$sefUrl       = '';
				$cacheSefUrls = $this->template->get('cachesefurls', false);

				foreach ($output->data as $url => $sefUrl)
				{
					if ($cacheSefUrls)
					{
						$this->saveSefUrl($url . $language, $sefUrl);
					}
				}

				return $sefUrl;
			}
		}

		// Get position of the forward slash
		$slashPosition = strpos($url, '/');

		if ($slashPosition > 0 || $slashPosition === false)
		{
			$url = '/' . $url;
		}

		return $this->domainname . $url;
	}

	/**
	 * Create a list of SEF URLs.
	 *
	 * @param   array   $urls      An array of URLs to convert.
	 * @param   string  $language  The language to get the SEF URL for.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   7.1.0
	 *
	 * @throws  CsviException
	 */
	public function getSefUrls($urls, $language = '')
	{
		$parseUrl = json_encode($urls);
		$language = substr($language, 0, 2);
		$language = $language ? '&lang=' . $language : '';
		$options  = new Joomla\Registry\Registry;
		$options->set('follow_location', false);
		$http     = JHttpFactory::getHttp($options, array('curl', 'stream'));
		$result   = $http->post(
			$this->domainname . '/index.php?option=com_csvi&task=sefs.getsef&format=json' . $language, array('parseurl' => $parseUrl), null, 5
		);
		$output   = json_decode($result->body);

		// Check if the status is different from 200
		if ((int) $result->code !== 200)
		{
			throw new CsviException(JText::sprintf('COM_CSVI_SEF_URL_INCORRECT_HTTP_HEADER', $result->code, $result->body));
		}

		if (is_object($output) && $output->success && (is_array($output->data) || is_object($output->data)))
		{
			foreach ($output->data as $url => $sefUrl)
			{
				$sefUrl .= $this->template->get('producturl_suffix', '');

				$this->saveSefUrl($url . $language, $sefUrl);
			}
		}

		return true;
	}

	/**
	 * Load SEF URL from database.
	 *
	 * @param   string  $url  The URL to change to SEF.
	 *
	 * @return  string  The SEF URL from the database or empty if nothing is found.
	 *
	 * @since   7.1.0
	 */
	private function loadSefUrl($url)
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('sefurl'))
			->from($this->db->quoteName('#__csvi_sefurls'))
			->where($this->db->quoteName('plainurl') . ' = ' . $this->db->quote($url));

		$this->db->setQuery($query);

		return $this->db->loadResult();
	}

	/**
	 * Store a SEF URL in the database.
	 *
	 * @param   string  $plainUrl  The non-SEF URL to store.
	 * @param   string  $sefUrl    The SEF URL to store.
	 *
	 * @return  void
	 *
	 * @since   7.1.0
	 */
	private function saveSefUrl($plainUrl, $sefUrl)
	{
		$query = $this->db->getQuery(true)
			->insert($this->db->quoteName('#__csvi_sefurls'))
			->columns(
				array(
					'plainurl',
					'sefurl',
				)
			)
			->values(
				$this->db->quote($plainUrl) . ',' . $this->db->quote($sefUrl)
			);
		$this->db->setQuery($query)->execute();
	}
}
