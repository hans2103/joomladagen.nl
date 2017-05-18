<?php
/**
 * @package         Regular Labs Library
 * @version         17.5.13702
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library\Condition;

defined('_JEXEC') or die;

use GeoIp;
use JLog;

/**
 * Class Geo
 * @package RegularLabs\Library\Condition
 */
abstract class Geo
	extends \RegularLabs\Library\Condition
{
	var $geo = null;

	public function getGeo($ip = '')
	{
		if ($this->geo !== null)
		{
			return $this->geo;
		}

		if (!file_exists(JPATH_LIBRARIES . '/geoip/geoip.php'))
		{
			return false;
		}

		require_once JPATH_LIBRARIES . '/geoip/geoip.php';

		$geo = new GeoIp($ip);

		$this->geo = $geo->get();

		if (JDEBUG)
		{
			JLog::addLogger(['text_file' => 'regularlabs_geoip.log.php'], JLog::ALL, ['regularlabs_geoip']);
			JLog::add(json_encode($this->geo), JLog::DEBUG, 'regularlabs_geoip');
		}

		return $this->geo;
	}
}
