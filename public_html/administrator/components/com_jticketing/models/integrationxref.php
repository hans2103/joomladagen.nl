<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

require_once JPATH_SITE . '/components/com_jticketing/models/integrationxref.php';
jimport('joomla.application.component.modeladmin');

/** integrationxref model.
 * 
 * @since  1.6
 */

JLoader::import('com_jticketing.models.Integrationxref', JPATH_SITE . '/components');
