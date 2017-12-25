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
 * Profile controller.
 *
 * @package  JDiDEAL
 * @since    4.0
 */
class JdidealgatewayControllerProfile extends JControllerForm
{
	/**
	 * Change the active iDEAL type.
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 *
	 * @throws  Exception
	 */
	public function change()
	{
		$app = JFactory::getApplication();

		$jform = $this->input->get('jform', array(), 'array');

		if (array_key_exists('psp', $jform))
		{
			$app->setUserState('profile.psp', $jform['psp']);
		}

		$id = $this->input->getInt('id');
		$app->redirect('index.php?option=com_jdidealgateway&task=profile.edit&id=' . $id);
	}
}
