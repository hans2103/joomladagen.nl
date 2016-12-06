<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */
 
defined('_JEXEC') or die;
jimport('joomla.application.component.controlleradmin');
class ObSocialSubmitControllerPlugins extends JControllerAdmin
{
	protected $text_prefix='COM_OBSOCIALSUBMIT_PLUGINS';

	public function __construct($config=array()){
		parent::__construct($config);
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Plugins', $prefix = 'ObSocialSubmitModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}
