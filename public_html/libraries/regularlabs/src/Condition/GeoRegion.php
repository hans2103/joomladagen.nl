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

/**
 * Class GeoRegion
 * @package RegularLabs\Library\Condition
 */
class GeoRegion
	extends Geo
{
	public function pass()
	{
		if (!$this->getGeo() || empty($this->geo->countryCode) || empty($this->geo->regionCodes))
		{
			return $this->_(false);
		}

		$regions = $this->geo->regionCodes;
		array_walk($regions, function (&$value)
		{
			$value = $this->geo->countryCode . '-' . $value;
		});

		return $this->passSimple($regions);
	}
}
