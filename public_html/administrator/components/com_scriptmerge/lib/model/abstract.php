<?php
/**
 * Joomla! Yireo Library
 *
 * @author    Yireo (http://www.yireo.com/)
 * @package   YireoLib
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 * @version   0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import the loader
require_once dirname(dirname(__FILE__)) . '/loader.php';

/**
 * Yireo Abstract Model
 * Parent class to easily maintain backwards compatibility
 *
 * @package Yireo
 */
class YireoAbstractModel extends JModelLegacy
{
	/**
	 * @var JApplicationCms
	 */
	protected $app;

	/**
	 * @var JApplicationCms
	 * @deprecated Use $this->app instead
	 */
	protected $application;

	/**
	 * @var JInput
	 */
	protected $input;

	/**
	 * @var JInput
	 * @deprecated Use $this->input instead
	 */
	protected $jinput;

	/**
	 * @var JInput
	 */
	protected $_input;

	/**
	 * Constructor
	 *
	 * @return mixed
	 */
	public function __construct()
	{
		// Useful variables
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;

		// Deprecated variables
		$this->application = $this->app;
		$this->_input = $this->input;
		$this->jinput = $this->input;

		return parent::__construct();
	}
}
