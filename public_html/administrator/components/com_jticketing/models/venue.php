<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

use Joomla\Utilities\ArrayHelper;

// Load frontend adform model
require_once JPATH_SITE . '/components/com_jticketing/models/venueform.php';

/**
 * Jticketing model.
 *
 * @since  1.6
 */
class JticketingModelVenue extends JticketingModelVenueForm
{
}
