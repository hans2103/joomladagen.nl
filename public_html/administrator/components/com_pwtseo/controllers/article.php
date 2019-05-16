<?php
/**
 * @package    Pwtseo
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2019 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;

/**
 * The article controller
 *
 * @since  1.0.2
 */
class PWTSEOControllerArticle extends FormController
{
	/**
	 * Method to run batch operations.
	 *
	 * @param   BaseDatabaseModel $modelLegacy The model of the component being processed
	 *
	 * @return  boolean   True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.0.2
	 * @throws  Exception
	 */
	public function batch($modelLegacy)
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$filter = InputFilter::getInstance();

		/** @var ContentModelArticle $model */
		BaseDatabaseModel::addIncludePath(JPATH_ROOT . '/administrator/components/com_content/models');
		$model = BaseDatabaseModel::getInstance('Article', 'ContentModel', array());

		$vars = $this->input->post->get('batch', array(), 'array');
		$cid  = $this->input->post->get('cid', array(), 'array');

		$data = array('metadesc' => $filter->clean($vars['metadesc'], 'HTML'));

		$errors = false;

		foreach ($cid as $id)
		{
			$data['id'] = (int) $id;

			if (!isset($vars['override_metadesc']) || $vars['override_metadesc'] !== '1')
			{
				$modelData = $model->getItem($id);

				if (strlen($modelData->metadesc) > 0)
				{
					continue;
				}
			}

			if (!$model->save($data))
			{
				$errors = true;
				Factory::getApplication()->enqueueMessage(Text::sprintf('COM_PWT_ERRORS_FAILED_TO_SAVE_METADESC', $id));
			}
		}

		if (!$errors)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_PWT_BATCH_APPLIED_METADESC'));
		}

		$this->setRedirect(Route::_('index.php?option=com_pwtseo&view=articles' . $this->getRedirectToListAppend(), false));

		return true;
	}

	/**
	 * Method to run auto fill the meta description and keywords for articles that don't have any
	 *
	 * @since   1.3.0
	 * @throws  Exception
	 */
	public function autofillmeta()
	{
		// The plugin holds the blacklist data and the metadesc length
		$pluginParams  = new Registry(PluginHelper::getPlugin('system', 'pwtseo')->params);
		$metadescCount = $pluginParams->get('count_max_metadesc', 160);

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array(
						'id',
						'language',
						'introtext',
						'metakey',
						'metadesc'
					)
				)
			)
			->from($db->quoteName('#__content'))
			->where('(' . $db->quoteName('metakey') . ' = "" OR ' . $db->quoteName('metadesc') . ' = "")');

		$articles = $db->setQuery($query, 0, 200)->loadObjectList();

		// Build the global blacklist for re-use
		$blacklist = '';
		$params    = $pluginParams->toArray();

		foreach ($params as $key => $val)
		{
			if (stripos($key, 'blacklist_') === 0)
			{
				$blacklist .= ' ' . $val;
			}
		}

		$processed = 0;

		// Overwrite the meta data where applicable and save it
		foreach ($articles as $article)
		{
			if (!$article->metakey)
			{
				$article->metakey = implode(
					' ',
					PWTSEOHelper::getMostCommenWords(
						$article->introtext,
						$article->language === '*' ? $blacklist : $pluginParams->get('blacklist_' . $article->language, '')
					)
				);
			}

			if (!$article->metadesc)
			{
				$article->metadesc = rtrim(HTMLHelper::_('string.truncate', preg_replace('/{+?.*?}+?/i', ' ', $article->introtext), $metadescCount, true, false), '.');
			}

			try
			{
				$db->updateObject('#__content', $article, array('id'));
				$processed++;
			}
			catch (Exception $e)
			{
			}
		}

		Factory::getApplication()->enqueueMessage(Text::sprintf('COM_PWT_ARTICLES_APPLIED_METADATA', $processed));
		$this->setRedirect(Route::_('index.php?option=com_pwtseo&view=articles', false));
	}
}
