<?php
/**
 * @package         Sourcerer
 * @version         7.1.6PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

if (!is_file(__DIR__ . '/vendor/autoload.php'))
{
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

use RegularLabs\Sourcerer\Plugin;

/**
 * Plugin that replaces Sourcerer code with its HTML / CSS / JavaScript / PHP equivalent
 */
class PlgSystemSourcerer extends Plugin
{
	public $_alias       = 'sourcerer';
	public $_title       = 'SOURCERER';
	public $_lang_prefix = 'SRC';

	public $_can_disable_by_url = false;
	public $_page_types         = ['html', 'feed', 'pdf', 'ajax', 'xml', 'raw', 'json'];
}
