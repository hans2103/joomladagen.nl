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

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

/**
 * The article controller
 *
 * @since  1.2.0
 */
class PWTSEOControllerMenu extends FormController
{
	/**
	 * Method to run batch operations.
	 *
	 * @param   BaseDatabaseModel $model The model of the component being processed.
	 *
	 * @return  boolean   True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.2.0
	 */
	public function batch($model)
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$filter = InputFilter::getInstance();

		Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_menus/tables');
		/** @var MenusModelItem $model */
		BaseDatabaseModel::addIncludePath(JPATH_ROOT . '/administrator/components/com_menus/models');
		$model = BaseDatabaseModel::getInstance('Item', 'MenusModel', array());

		$vars = $this->input->post->get('batch', array(), 'array');
		$cid  = $this->input->post->get('cid', array(), 'array');

		$data = array('metadesc' => $filter->clean($vars['metadesc'], 'HTML'));

		$errors = false;

		foreach ($cid as $id)
		{
			$data['id'] = (int) $id;
			$modelData  = $model->getItem($id);
			$params     = new Registry($modelData->params);

			if (!isset($vars['override_metadesc']) || $vars['override_metadesc'] !== '1')
			{
				if (strlen($params->get('menu-meta_description')) > 0)
				{
					continue;
				}
			}

			$params->set('menu-meta_description', $data['metadesc']);
			$modelData->params = $params->toArray();

			if (!$model->save((array) $modelData))
			{
				$errors = true;
				Factory::getApplication()->enqueueMessage(Text::sprintf('COM_PWT_ERRORS_FAILED_TO_SAVE_METADESC', $id));
			}
		}

		if (!$errors)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_PWT_BATCH_APPLIED_METADESC'));
		}

		$this->setRedirect(Route::_('index.php?option=com_pwtseo&view=menus' . $this->getRedirectToListAppend(), false));

		return true;
	}
}
