<?php
/**
 * @package   OSMyLicensesManager
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Joomlashack.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\Framework\AutoLoader;

defined('_JEXEC') or die();

// Alledia Framework
if (!defined('ALLEDIA_FRAMEWORK_LOADED')) {
    $allediaFrameworkPath = JPATH_SITE . '/libraries/allediaframework/include.php';

    if (file_exists($allediaFrameworkPath)) {
        require_once $allediaFrameworkPath;
    } else {
        JFactory::getApplication()
            ->enqueueMessage('[OSMyLicensesManager] Alledia framework not found', 'error');
    }
}

// Extension's library
if (!defined('OSMYLICENSESMANAGER_LOADED')) {
    AutoLoader::register('Alledia\\OSMyLicensesManager', JPATH_SITE . '/plugins/system/osmylicensesmanager/library');

    define('OSMYLICENSESMANAGER_LOADED', 1);
}
