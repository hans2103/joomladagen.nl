<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$this->video_params['divId'] = "jticketing_video";

$dispatcher = JDispatcher::getInstance();
JPluginHelper::importPlugin('tjvideo', $this->video_params['plugin']);
$result = $dispatcher->trigger('renderPluginHTML', array($this->video_params));

echo $result[0];
